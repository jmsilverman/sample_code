#!/anaconda/bin/python
"""
 reads in photometry of requested objects from text files or
    a query of the SNDB
 fits the late-time decline of each LC with a line using either
    a starting point chosen by a user click or an inflection
    point from a modified logistic function fit to the entire LC
 saves the late-time slopes to a text file
"""

# import stuff
import glob
import os
import sys
import numpy as np
import matplotlib.pyplot as plt
from scipy.optimize import curve_fit
from scipy.interpolate import interp1d

# define linear function
def line(x, a, b):
    return a + b*x

# define modified logistic function
def func(x, a, b, c, d, e):
    return 1. / (1. + np.exp((x-a)/b)) * c + (d + e*x)
# define derivative of above function
def funcprime(x, a, b, c, d, e):
    return (c/b) * (1. / (1. + np.exp((x-a)/b))) * (1. - 1. / (1. + np.exp((x-a)/b))) + e
# define derivative of above derivative
def funcprimeprime(x, a, b, c, d, e):
    return (c/b/b) * (1. / (1. + np.exp((x-a)/b))) * (1. / (1. + np.exp((a-x)/b))) * ((1. / (1. + np.exp((a-x)/b))) - (1. / (1. + np.exp((x-a)/b))))


# SNDB query code which will save SQL search output to local text
#    files and then return a list of the text file filenames
def query_sndb(dir):
    #FIXME
    file_list = []
    print 'in query_sndb()'
    return file_list


# main program
def main():
  # set up interactive plotting
  plt.interactive(True)


  # define directory of LCs
  lc_dir = 'light_curves/'

  
  # grab the list of command line arguments
  args = sys.argv[1:]

  # make sure user specified a way of grabbing the data
  if not args:
    print 'usage: {--sndb|--files}  [--interactive]'
    sys.exit(1)

  # use the SNDB to get the data
  if args[0] == '--sndb':
    lc_list = query_sndb(lc_dir)
    
  # use already existing local text files to get the data
  elif args[0] == '--files':
    lc_list = glob.glob(lc_dir+'*.dat')

  # if neither option selected, bail out
  else:
    print 'usage: {--sndb|--files}  [--interactive]'
    sys.exit(1)
  del args[0]


  # see if the interactive flag exists
  interactive = False
  if args:
    if args[0] == '--interactive':
      interactive = True

      
  # define output filename
  if interactive:
    slopefile = "late_slopes_interactive.txt"
  else:
    slopefile = "late_slopes_auto.txt"
  # open and clear output file
  f = open(slopefile, 'w')
  # print header
  f.write('# object filt slope      err\n')
  f.write('#             (mag/d)    (mag/d)\n')

  
  # go through each LC
  for lc in lc_list:

    # get object name and filter
    dirname, filename = os.path.split(lc)
    obj, filt, ext = filename.split('.')
    print obj+' '+filt

    
    # read-in LC data
    jd,mag,err = np.loadtxt(lc).T
    
    # convert to MJD
    mjd = jd - 2400000.5
    # scale x-value feature relative to first data point
    first_mjd = mjd[0]
    mjd_scaled = mjd - first_mjd
    

    # make a figure for the LC
    plt.figure(1)
    # clear Fig. 1
    plt.clf()
    # plot LC w/ errorbars and w/ y-axis reversed (black x's)
    plt.errorbar(mjd_scaled, mag, yerr=err, fmt='ks', markersize=7, label='Data')
    plt.gca().invert_yaxis()
    plt.gca().set_xlim([mjd_scaled.min()-5,mjd_scaled.max()+5])
    plt.xlabel('Modified Julian Date - '+str(first_mjd))
    plt.ylabel('Observed '+filt+' Magnitude')
    plt.title(obj+' '+filt+' light curve')
    plt.show()


    # interactively choose starting point of late-time linear decline
    if interactive:

      # ask for a click and don't timeout
      print 'click once'
      [[x1,y1]] = plt.ginput(1, timeout=0)
      # plot clicked point
      #plt.plot(x1,y1,'mo')
      # plot vertical line at clicked x-value
      plt.plot([x1,x1], [mag.min()-1,mag.max()+1], 'm-', linewidth=1.5)
      #print 'you clicked at',x1,y1

      # save data on the tail, beyond the clicked x-value
      jd_tail = mjd_scaled[mjd_scaled > x1]
      mag_tail = mag[mjd_scaled > x1]
      err_tail = err[mjd_scaled > x1]

    # automatically choose starting point of late-time linear decline
    else:
      # define initial params for modified logistic function
      p0 = [60, 7, -1, (mag.min()+mag.max())/2., 0.01]
      # hardcode initial params for a couple objects
      
      # fit a function to the entire LC, w/ measurement errors if we have them
      if err.sum() != 0:
        params,cov = curve_fit(func, mjd_scaled, mag, p0=p0, bounds=([2.,1.,-4.,mag.min(),0.0005], [180.,15.,2.,mag.max(),0.3]), \
                               sigma=err, absolute_sigma=True)
      else:
        params,cov = curve_fit(func, mjd_scaled, mag, p0=p0, bounds=([2.,1.,-4.,mag.min(),0.0005], [180.,15.,2.,mag.max(),0.3]))
      # display best-fit params
      #print params
      
      # overplot best-fit function (magenta)
      xvals = np.linspace(mjd_scaled.min(), mjd_scaled.max(), num=100)
      plt.plot(xvals, func(xvals, params[0], params[1], params[2], params[3], params[4]), 'y-', linewidth=2, label='Modified Logistic Function')

      # find the inflection point at the beginning of the tail
      spot = np.argmin(funcprimeprime(xvals, params[0], params[1], params[2], params[3], params[4]))
      inflec = xvals[spot]
      plt.plot(inflec,func(inflec, params[0], params[1], params[2], params[3], params[4]), 'm^', markersize=10)
      plt.plot([inflec,inflec], [mag.min()-1,mag.max()+1], 'm-', linewidth=1.5)
      
      
      # save data on the tail, beyond the last inflection point
      jd_tail = mjd_scaled[mjd_scaled > inflec]
      mag_tail = mag[mjd_scaled > inflec]
      err_tail = err[mjd_scaled > inflec]
      # if only 1 point beyond the last inflection point, grab the second to last point as well (so we can fit a line to them)
      if np.size(jd_tail) == 1:
        jd_tail = mjd_scaled[-2:]
        mag_tail = mag[-2:]
        err_tail = err[-2:]
        
      
    # highlight data on the tail (blue circles)
    plt.errorbar(jd_tail, mag_tail, yerr=err_tail, fmt='bs', markersize=7, label='Late-Time Data')

    # save first and last data points on the tail
    endpoints = np.array([jd_tail[0], jd_tail[-1]])

    # fit a line to the tail, w/ measurement errors if we have them
    if err_tail.sum() != 0:
      params,cov = curve_fit(line, jd_tail, mag_tail, sigma=err_tail)
    else:
      params,cov = curve_fit(line, jd_tail, mag_tail)

    # calculate the standard deviation errors on the parameters from the covariance matrix
    if jd_tail.size > 2:
      params_err = np.sqrt(np.diag(cov))
    else:
      # standard deviation errors are zero if we fit a line to exactly 2 points
      params_err = params*0


    # overplot linear fit to the tail (red solid line)
    plt.plot(endpoints, line(endpoints,params[0],params[1]), 'r-', linewidth=2, label='Linear Fit')

    # calculate the RMSE from the linear fit
    mag_predicted = params[0] + params[1]*jd_tail
    squared_diff = (mag_predicted - mag_tail)**2
    RMSE = np.sqrt(squared_diff.sum() / jd_tail.size)
    # overplot RMSE of linear fit (green dotted line)
    #plt.plot(endpoints, line(endpoints,params[0],params[1]) + RMSE, 'g:', linewidth=3, label='RMSE')
    #plt.plot(endpoints, line(endpoints,params[0],params[1]) - RMSE, 'g:', linewidth=3)
    
    # overplot the Co decay rate (cyan dashed line)
    x1 = endpoints.sum()/2.
    y1 = line(x1,params[0],params[1])
    #plt.plot(endpoints, y1 + 0.0097*(endpoints - x1), 'c--', linewidth=2, label='Co decay')

    # add a legend and show the plot
    plt.legend(loc=0)
    plt.show()
    

    # make a second figure that's a zoom-in on LC tail
    plt.figure(2)
    # plot LC tail data
    plt.errorbar(jd_tail, mag_tail, yerr=err_tail, fmt='bs', markersize=7, label='Late-Time Data')
    plt.gca().invert_yaxis()
    plt.gca().set_xlim([jd_tail.min()-5,jd_tail.max()+5])
    plt.xlabel('Modified Julian Date - '+str(first_mjd))
    plt.ylabel('Observed '+filt+' Magnitude')
    plt.title(obj+' '+filt+' light curve')
    # overplot linear fit
    plt.plot(endpoints, line(endpoints,params[0],params[1]), 'r-', linewidth=2, label='Linear Fit')
    # overplot RMSE
    plt.plot(endpoints, line(endpoints,params[0],params[1]) + RMSE, 'g:', linewidth=3, label='RMSE')
    plt.plot(endpoints, line(endpoints,params[0],params[1]) - RMSE, 'g:', linewidth=3)
    # overplot Co decay rate
    plt.plot(endpoints, y1 + 0.0097*(endpoints - x1), 'c--', linewidth=2, label='Co decay')
    # add a legend and show the plot
    plt.legend(loc=0)
    plt.show()


    # save the linear fit's slope and error
    f.write(obj+(9-len(obj))*' '+filt+(5-len(filt))*' '+'{0:.8f} {1:.8f}\n'.format(params[1],params_err[1]))

    # continue to next LC
    #aa = raw_input('Press <enter> to continue')
    print 'Click anywhere on Fig. 2 to continue'
    aa = plt.ginput(1, timeout=0)

    # clear Fig. 2
    plt.clf()

  # close the output file
  f.close()
  
if __name__ == '__main__':
  main()
