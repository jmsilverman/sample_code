# FUNCTION: classify
#
# SNIDs all spectra (with signal) in the input directory to see if there are
#     any "reasonable" matches. A spectrum is considered to have signal if
#     the median flux is greater than 0.5-sigma from zero.
#
# Creates a classifications file (SNID_classifications_<DATE>.txt) that gets
#     appended as the program runs so the user can track new classifications.
#
# Can run anywhere.
#
# Optional Inputs:
#         in_dir    - input directory where the spectra to SNID live, default
#                     is local directory
#         extension - file extension for spectra to SNID, default is '.flm'
#         tempdir   - directory that holds the SNID templates you want to use,
#                     default is the default SNID template set plus the new
#                     SDSS non-SN templates
#                     (/home/ts3/jsilverman/SNID_templates_default_plus_SDSS/)
#                     but you might want to use the jsilv full template set
#                     (/home/ts3/jsilverman/SNID_templates_full/) instead
#
# Output File:
#         SNID_classifications_<DATE>.txt - text file, named using the current
#                                           system date, that contains the
#                                           classifications for each spectrum
#
# Created: 07/07/15, JMS, JSilverman@astro.as.utexas.edu
# Edited:  11/10/15, JMS (added tempdir as optional input)
# Edited:  02/22/16, JMS (added two different SNID runs for MW vs. extragalactic templates)
#

import time
import glob
import os
import numpy
from pylab import *

def classify(in_dir='.', extension='.flm', tempdir='/home/ts3/jsilverman/SNID_templates_default_plus_SDSS/'):

    # get start time
    start_time = time.time()

    # initialize counts
    tot_spec = 0
    good_spec = 0

    # get current system date
    cur_date = time.strftime("%Y%m%d")
    # define output filename
    classificationsfile = "SNID_classifications_" + cur_date + ".txt"
    #print classificationsfile
    # clear output file
    f = open(classificationsfile, 'w')
    # print header
    f.write('file                    type avg_z (err)   avg_age (err) rlap  best_match_template (subtype) z (err) t (err)    N_good_matches\n')
    f.close()
    
    # get list of spectra
    spectra = sorted(glob.glob(in_dir+'/*'+extension))
    #print spectra

    # go through each spectrum
    for spectrum in spectra:
        #print ''
        print spectrum
        tot_spec+=1

        # read-in spectrum
        wave,flux = numpy.genfromtxt(spectrum,comments='#',dtype=None,unpack=True)

        # DEPRECATED: SNID only if the median flux is greater than 0.5-sigma above zero
        # if numpy.median(flux) > 0.5*numpy.std(flux):
        #
	# SNID only if mean flux is greater than 0.3
	if numpy.mean(flux) > 0.3:

            # define min rlap
            rlapstring = 'rlapmin=5'                # default = 5
	    #rlapstring = rlapstring+' lapmin=0.3'   # default = 0.4

            # define output filename
            dirname, filename = os.path.split(spectrum)
            snidfile_noext, dummy = os.path.splitext(filename)
            snidfile = snidfile_noext + "_snid.output"
            #print snidfile



            # SNID run 1 is for stars (i.e., z=0 and only M-stars and C-stars)
            snidcommand1 = "snid " + rlapstring + " forcez=0 usetype=M-star,C-star tempdir=" + tempdir + " plot=0 inter=0 verbose=0 " + spectrum
            #print snidcommand1
            # run SNID command
            os.system(snidcommand1)

            # if SNID run 1 output exists (thus, there must have been at least 1 'good' match)
            rlap1 = 0
            if os.path.isfile(snidfile):

                # read type fraction/redshift/age
                f = open(snidfile)
                snippet = f.readlines()[39:71]
                f.close()
                # write to a temp file
                f = open('temp0000','w')
                for line in snippet:
                    f.write(line)
                f.close()
                # save type fraction/redshift/age for SNID run 1
                types1 = numpy.genfromtxt('temp0000', names=['sntype', 'ntemp', 'fraction', 'slope', 'avgsnidz',
                                                            'avgsnidz_err', 'avgsnidage', 'avgsnidage_err'], 
                                         comments='#', dtype=None, unpack=True)
                # save only lines with subtypes
                types1 = types1[:][[1,2,3,4,5,6,7,9,10,11,13,14,15,17,18,19,20,22,23,24,25,26,27]]
                # remove temp file
                os.system('\\rm temp0000')

                # get subtype with highest fraction and the fraction value 
                highest_frac1 = max(types1['fraction'])
                highest_index1 = numpy.argmax(types1['fraction'])
                highest_type1 = types1['sntype'][highest_index1]
                highest_z1 = types1['avgsnidz'][highest_index1]
                #print highest_frac1
                #print highest_index1
                #print highest_type1
                #print highest_z1
                #raw_input("SNID run 1")

                # save best matches of SNID run 1
                best_matches1 = numpy.genfromtxt(snidfile, names=['number', 'snidsn', 'snidtype', 'lap', 'rlap', 'snidz', 
                                                                  'snidz_err', 'snidage', 'snidage_flag', 'grade'], 
                                                 comments='#', skip_header=73, dtype=None, unpack=True, invalid_raise=False)
            
                # get number of 'good' matches with correct (highest fraction) subtype
                n_goodSNID1 = sum((best_matches1['grade']=="good") * (best_matches1['snidtype']==highest_type1))

                # get best 'good' match with correct (highest fraction) subtype
                best_match1 = next(x for x in best_matches1 if (x['grade'] == 'good') and (x['snidtype'] == highest_type1))

                # save best 'good' match's info
                bestmatchSNID1 = str(best_match1['rlap'])+'  '+best_match1['snidsn']+' ('+best_match1['snidtype']+') '+ \
                    str(best_match1['snidz'])+' ('+str(best_match1['snidz_err']) +') '+ str(best_match1['snidage'])+' ('+ \
                    str(types1['avgsnidage_err'][highest_index1])+') '
                rlap1 = best_match1['rlap']

                # remove SNID output file
                os.system('\\rm '+snidfile)



            # SNID run 2 is for extragalactic objects (i.e., z!=0 and neither M-stars nor C-stars)
            snidcommand2 = "snid " + rlapstring + " avoidtype=M-star,C-star,Ic-broad tempdir=" + tempdir + " plot=0 inter=0 verbose=0 " + spectrum
            #print snidcommand2
            # run SNID command
            os.system(snidcommand2)

            # if SNID run 2 output exists (thus, there must have been at least 1 'good' match)
            rlap2 = 0
            if os.path.isfile(snidfile):

                # read type fraction/redshift/age
                f = open(snidfile)
                snippet = f.readlines()[39:71]
                f.close()
                # write to a temp file
                f = open('temp0000','w')
                for line in snippet:
                    f.write(line)
                f.close()
                # save type fraction/redshift/age for SNID run 2
                types2 = numpy.genfromtxt('temp0000', names=['sntype', 'ntemp', 'fraction', 'slope', 'avgsnidz',
                                                            'avgsnidz_err', 'avgsnidage', 'avgsnidage_err'], 
                                         comments='#', dtype=None, unpack=True)
                # save only lines with subtypes
                types2 = types2[:][[1,2,3,4,5,6,7,9,10,11,13,14,15,17,18,19,20,22,23,24,25,26,27]]
                # remove temp file
                os.system('\\rm temp0000')

                # get subtype with highest fraction and the fraction value 
                highest_frac2 = max(types2['fraction'])
                highest_index2 = numpy.argmax(types2['fraction'])
                highest_type2 = types2['sntype'][highest_index2]
                highest_z2 = types2['avgsnidz'][highest_index2]
                #print highest_frac2
                #print highest_index2
                #print highest_type2
                #print highest_z2
                #raw_input("SNID run 2")

                # save original highest index and type for SNID run 2
                highest_index_orig = highest_index2
                highest_type_orig = highest_type2
                
                # make sure redshift is != 0
                keep_going = 1
                while ((highest_z2 < 0.001) and (keep_going == 1)):

                    # if not, then find (sub)type with next-highest fraction
                    #print types2['fraction']
                    types2['fraction'][highest_index2] = 0.
                    #print types2['fraction']

                    highest_frac2 = max(types2['fraction'])
                    highest_index2 = numpy.argmax(types2['fraction'])
                    highest_type2 = types2['sntype'][highest_index2]
                    highest_z2 = types2['avgsnidz'][highest_index2]
                    #print highest_frac2
                    #print highest_index2
                    #print highest_type2
                    #print highest_z2
                    #raw_input("SNID run 2, next highest fraction")

                    # if no reasonable fits then use subtype with the original highest fraction
                    if highest_frac2 == 0:
                        highest_index2 = highest_index_orig
                        highest_type2 = highest_type_orig
                        keep_going = 0

                # save best matches of SNID run 2
                best_matches2 = numpy.genfromtxt(snidfile, names=['number', 'snidsn', 'snidtype', 'lap', 'rlap', 'snidz', 
                                                                  'snidz_err', 'snidage', 'snidage_flag', 'grade'], 
                                                 comments='#', skip_header=73, dtype=None, unpack=True, invalid_raise=False)
            
                # get number of 'good' matches with correct (highest fraction) subtype
                n_goodSNID2 = sum((best_matches2['grade']=="good") * (best_matches2['snidtype']==highest_type2))

                # get best 'good' match with correct (highest fraction) subtype
                best_match2 = next(x for x in best_matches2 if (x['grade'] == 'good') and (x['snidtype'] == highest_type2))

                # save best 'good' match's info
                bestmatchSNID2 = str(best_match2['rlap'])+'  '+best_match2['snidsn']+' ('+best_match2['snidtype']+') '+ \
                    str(best_match2['snidz'])+' ('+str(best_match2['snidz_err']) +') '+ str(best_match2['snidage'])+' ('+ \
                    str(types2['avgsnidage_err'][highest_index2])+') '
                if (keep_going == 1):
                    rlap2 = best_match2['rlap']
                else:
                    rlap2 = 0



            # make sure one of the 2 SNID runs was good
            if ((rlap1+rlap2) > 0):
                # compare SNID run 1 to run 2 and take match with higher rlap
                if rlap1 > rlap2:
                    # save avg classification info for subtype with highest fraction
                    classification = filename+' '+types1['sntype'][highest_index1]+' '+str(types1['avgsnidz'][highest_index1])+' ('+ \
                        str(types1['avgsnidz_err'][highest_index1])+ ') '+str(types1['avgsnidage'][highest_index1])+' ('+ \
                        str(types1['avgsnidage_err'][highest_index1])+')  '+bestmatchSNID1+str(n_goodSNID1)+'\n'

                    # re-run SNID to save best-matching template spectrum
                    snidcommand1 = "snid " + rlapstring + " fluxout=" + str(best_match1['number']) + " forcez=0 usetype=M-star,C-star tempdir=" + \
                        tempdir + " plot=0 inter=0 verbose=0 " + spectrum
                    #print snidcommand1
                    # run SNID command
                    os.system(snidcommand1)
                    bestnum = best_match1['number']
                    bestmatchSNID = bestmatchSNID1
                else:
                    # save avg classification info for subtype with highest fraction
                    classification = filename+' '+types2['sntype'][highest_index2]+' '+str(types2['avgsnidz'][highest_index2])+' ('+ \
                        str(types2['avgsnidz_err'][highest_index2])+ ') '+str(types2['avgsnidage'][highest_index2])+' ('+ \
                        str(types2['avgsnidage_err'][highest_index2])+')  '+bestmatchSNID2+str(n_goodSNID2)+'\n'

                    # re-run SNID to save best-matching template spectrum
                    snidcommand2 = "snid " + rlapstring + " fluxout=" + str(best_match2['number']) + " avoidtype=M-star,C-star,Ic-broad tempdir=" + \
                        tempdir + " plot=0 inter=0 verbose=0 " + spectrum
                    #print snidcommand2
                    # run SNID command
                    os.system(snidcommand2)
                    bestnum = best_match2['number']
                    bestmatchSNID = bestmatchSNID2



                # make a comparison plot of the data and best-matching template
                #
                # read-in data used by SNID
                wave1,flux1 = numpy.genfromtxt(snidfile_noext+"_snidflux.dat",comments='#',dtype=None,unpack=True)
                # convert NAN to zeros
                flux1[isnan(flux1)] = 0.

                # read-in best match
                if bestnum > 9:
                    compnum = str(bestnum)
                else:
                    compnum = '0'+str(bestnum)
                bestmatchfile = snidfile_noext+"_comp00"+compnum+"_snidflux.dat"
                wave2,flux2 = numpy.genfromtxt(bestmatchfile,comments='#',dtype=None,unpack=True)
                # convert NAN to zeros
                flux2[isnan(flux2)] = 0.

                # plot data
                plot(wave1,flux1,'k')
                # plot template
                plot(wave2,flux2,'r')
                # make plot pretty
                xlabel('Observed Wavelength (Ang.)')
                ylabel('Relative Flux')
                title(filename)
                text(max(numpy.concatenate((wave1,wave2))),max(numpy.concatenate((flux1,flux2))),\
                         'rlap='+bestmatchSNID,horizontalalignment='right',verticalalignment='top')
                savefig(snidfile_noext+".png")
                clf()
                good_spec+=1

                # open, append, and close output file
                f = open(classificationsfile, 'a')
                f.write(classification)
                f.close()
            
            # remove SNID output files
            if os.path.isfile(snidfile):
                os.system('\\rm *_snid.output')
            if os.path.isfile(bestmatchfile):
                os.system('\\rm *snidflux.dat')

    # remove last SNID file
    os.system('\\rm snid.param')

    # get ending time
    end_time = time.time()
    # calculate some numbers
    print 'Total exec time:   '+str(end_time-start_time)
    print 'Total spectra:     '+str(tot_spec)
    print 'Time per spectrum: '+str((end_time-start_time)/tot_spec)
    print 'Good spectra:      '+str(good_spec)
    print 'Percent good:      '+str(100.0*good_spec/float(tot_spec))+'%'
    print 'rlap cut used:     '+rlapstring

    return
