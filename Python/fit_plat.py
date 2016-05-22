# read in all V-band LCs
# click at beginning and end of plateau
# measure mean plateau mag and peak mag (on plateau)
# write mean and peak to "lc_plateau_params"
#

import glob
import os
import numpy as np
import matplotlib.pyplot as plt

# set up interactive plotting
plt.interactive(True)

# define output filename
lcparams = "lc_plateau_params"
# clear output file
f = open(lcparams, 'w')
# print header
f.write('# object m_V_plat   m_V_pk\n')
f.close()

# get all V-band LCs
lc_list = sorted(glob.glob('light_curves/*V.dat'))

# go through each LC
for lc in lc_list:
    
    print lc

    # read-in LC
    jd,mag,err = np.genfromtxt(lc,dtype=None,unpack=True)

    # plot LC
    plt.plot(jd,mag,'ro')
    plt.gca().invert_yaxis()
    plt.show()

    # ask for two points, and don't timeout
    #print 'click twice'
    [[x1,y1],[x2,y2]] = plt.ginput(2, timeout=0)
    plt.plot([x1,x2],[y1,y2],'gs')
    #print 'you clicked at',x1,y1
    #print 'and',x2,y2

    # highlight points on the plateau
    cut = (jd > x1) & (jd < x2)
    plt.plot(jd[cut],mag[cut],'bo')
    
    # get mean plateau mag
    platmean = np.mean(mag[cut])
    plt.plot([min(jd),max(jd)],[platmean,platmean])
    print platmean

    # get peak mag on plateau
    peak = min(mag[cut])
    plt.plot([min(jd),max(jd)],[peak,peak])
    print peak
    print ''

    # get object name
    dirname, filename = os.path.split(lc)
    obj, filt, ext = filename.split('.')
    
    # save mean and peak mags
    f = open(lcparams, 'a')
    f.write(obj+' '+str(platmean)+' '+str(peak)+'\n')
    f.close()

    # click to continue
    happy = plt.waitforbuttonpress()

    # clear plot
    plt.close()
