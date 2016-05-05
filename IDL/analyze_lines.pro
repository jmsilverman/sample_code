; analyze the spline and Gaussian fits to a bunch of lines (from measure_lines.pro)
; make a bunch of plots and calculate some values
; use spline for total flux, peak flux, peak vel
; use Gaussian for HWHM, HWZI (though currently ignoring fluxes and
; HWZI)
;
; open_plots - flag to automatically open PDFs of the plots
; connect - flag to connect all spectra of each object
; error - plot error bars on each point; DEFAULT is no error bars
;
; first choose which spectral range you want to view:
;      1: 4250-4800
;      Hgamma 4341 -- weak
;      Mg I] 4571
;
;      2: 4820-5500
;      Hbeta 4861
;      Fe II 5018
;
;      3: 5420-6100
;      Fe II 5527 -- weak (Maguire)
;      Na I D 5892 -- strong, blended doublet, blended with He I 5876
;
;      4: 6100-6490
;      [O I] 6300 -- strong
;      [O I] 6364 -- sometimes blended with [O I] 6300
;
;      5: 6400-6700
;      Halpha 6563 -- strong
;
;      6: 6600-7150
;      He I  6678
;      He I  7065
;
;      7: 7100-7430
;      [Fe II] 7155/[Fe II] 7172 -- blend
;      [Ca II] 7291 -- strong
;      [Ca II] 7324 -- sometimes blended with [Ca II] 7291
;
;      8: 7650-7900
;      ?????? 7700 -- telluric correction issues?
;      O I 7774 -- blended triplet, sometimes blended with stronger 7700 feature
;
;      9: 8400-8780
;      O I 8446 -- blended triplet, usually weak
;      Ca II 8498 -- usually blended with Ca II 8542
;      Ca II 8542 -- strong
;      Ca II 8662 -- strong
;
;
PRO analyze_lines,choice=choice,open_plots=open_plots,connect=connect,error=error

; define H_0
H0 = 73.0

; define common vars for use in al_helper.pro
COMMON SHARE, j, cut, filename_params, obj_params, feature_num, shape, objectlist, cutoff_values, cutoff, cutoff_param

; which spectral range to view
if ~keyword_set(choice) then begin
   print, 'Choose spectral range to measure'
   print, '      1: 4250-4800'
   print, '      2: 4820-5500'
   print, '      3: 5420-6100'
   print, '      4: 6100-6490'
   print, '      5: 6400-6700'
   print, '      6: 6600-7150'
   print, '      7: 7100-7430'
   print, '      8: 7650-7900'
   print, '      9: 8400-8780'
   choice = get_kbrd(1)
   print, choice
endif else choice = strcompress(choice,/rem)



; read in explosion ages (though we might not use these)
readcol, 'explosion_ages.txt', format = 'A,F,F', expl_filename, expl_age, expl_age_err, comment = '#'

; read in info for each object
readcol,'sndb_iip_info.dat',format='A,A,A,A,A,A,F,F,F,A,A,A,D,F,F,F,F,F,A,A',sn,objectlist,filename,utdatelist,instrume,spec_ref,$
        redd,z,dist,host1,host2,disc,expl,expl_err,mv_plat,mv_plat_err,mv_pk,mv_pk_err,refs,studied_refs,comment='#',delimiter=' '
objectlist = 'sn'+strlowcase(objectlist)
host = host1+' '+host2

; late-time LC decline rates
readcol, 'lc_params', format = 'A,A,I,F,F,F,F,F,F,A', lc_object, filt, n_pts, MJD_min, MJD_max, slope, slope_err, icept, icept_err, lc_phot_ref,  comment = '#'
; convert decline rates to mag / 100 d
slope = slope*100

; read in fit params
readcol,'params'+choice+'.txt',format='(A,F)',filename_params,disc_age,feature_num,shape,left,right,$
        f_tot,f_tot_err,spline_f_peak,spline_f_peak_err,spline_v,spline_v_err,$
        hwhm,hwhm_err,hwzi,hwzi_err,$
        gauss_1_f_peak,gauss_1_f_peak_err,gauss_1_lambda,gauss_1_lambda_err,gauss_1_hwhm,gauss_1_hwhm_err,gauss_1_hwzi,gauss_1_hwzi_err,$
        gauss_2_f_peak,gauss_2_f_peak_err,gauss_2_lambda,gauss_2_lambda_err,gauss_2_hwhm,gauss_2_hwhm_err,gauss_2_hwzi,gauss_2_hwzi_err,$
        gauss_3_f_peak,gauss_3_f_peak_err,gauss_3_lambda,gauss_3_lambda_err,gauss_3_hwhm,gauss_3_hwhm_err,gauss_3_hwzi,gauss_3_hwzi_err,$
        gauss_4_f_peak,gauss_4_f_peak_err,gauss_4_lambda,gauss_4_lambda_err,gauss_4_hwhm,gauss_4_hwhm_err,gauss_4_hwzi,gauss_4_hwzi_err,$
        comment = '#'
feature_num = round(feature_num)
shape = round(shape)

; initialize arrays of other params to associate with each fit
obj_params = strarr(n_elements(filename_params))
l_tot = dblarr(n_elements(filename_params))
l_tot_err = dblarr(n_elements(filename_params))
spline_l_peak = dblarr(n_elements(filename_params))
spline_l_peak_err = dblarr(n_elements(filename_params))
for i = 0, n_elements(filename_params)-1 do begin
   ; get object name
   pieces = strsplit(filename_params[i], '-', /extract)
   obj_params[i] = strlowcase(pieces[0])

   ; get object's info
   spot = (WHERE(objectlist EQ obj_params[i]))[0]

   ; if object has a Metric Distance (Mpc) from NED
   if dist[spot] NE 0 then begin

      ; calculate total luminosity
      l_tot[i] = f_tot[i]*1.d-15 * 4 * !pi * (dist[spot]*3.086d24)^2      ;/ 1.d38
      l_tot_err[i] = f_tot_err[i]*1.d-15 * 4 * !pi * (dist[spot]*3.086d24)^2 ;/ 1.d38

      ; calculate peak luminosity
      spline_l_peak[i] = spline_f_peak[i]*1.d-15 * 4 * !pi * (dist[spot]*3.086d24)^2      ;/ 1.d35
      spline_l_peak_err[i] = spline_f_peak_err[i]*1.d-15 * 4 * !pi * (dist[spot]*3.086d24)^2 ;/ 1.d35

   ; else, use z and H0
   endif else begin

      ; calculate total luminosity
      l_tot[i] = f_tot[i]*1.d-15 * 4 * !pi * (2.99792458d5*z[spot]/H0*3.086d24)^2      ;/ 1.d38
      l_tot_err[i] = f_tot_err[i]*1.d-15 * 4 * !pi * (2.99792458d5*z[spot]/H0*3.086d24)^2 ;/ 1.d38

      ; calculate peak luminosity
      spline_l_peak[i] = spline_f_peak[i]*1.d-15 * 4 * !pi * (2.99792458d5*z[spot]/H0*3.086d24)^2      ;/ 1.d35
      spline_l_peak_err[i] = spline_f_peak_err[i]*1.d-15 * 4 * !pi * (2.99792458d5*z[spot]/H0*3.086d24)^2 ;/ 1.d35

   endelse

endfor



; which param to cut between red and blue points
print, 'Choose param to cut between red and blue points'
print, '      1: M_V_plateau'
print, '      2: M_V_peak'
print, '      3: B-band decline rate'
print, '      4: V-band decline rate'
print, '      5: R-band/Unfiltered decline rate'
print, '      6: I-band decline rate'
choice2 = get_kbrd(1)
print, choice2

; define param to be used as cutoff between red and blue and the cutoff value and name
case float(choice2) of
   ; M_V_plateau: brighter than or equal to -16.3 mag (blue), fainter than -16.3 mag (red)
   1: begin
      cutoff_values = mv_plat
      cutoff = -16.3
      cutoff_param = 'MVplat'
   end
   ; M_V_peak: brighter than or equal to -16.6 mag (blue), fainter than -16.6 mag (red)
   2: begin
      cutoff_values = mv_pk
      cutoff = -16.6
      cutoff_param = 'MVpk'
   end
   ; B-band late-time LC decline rate: slower/shallower than or equal to Co decay (blue), faster/steeper than Co (red)
   3: begin
      cutoff_values = fltarr(n_elements(objectlist))
      for i = 0, n_elements(objectlist)-1 do begin
         spot = WHERE((lc_object EQ objectlist[i]) AND (filt EQ 'B'))
         if spot[0] NE -1 then cutoff_values[i] = slope[spot[0]]
      endfor
      cutoff = .62;0.97
      cutoff_param = 'Bslope'
   end
   ; V-band late-time LC decline rate: slower/shallower than or equal to Co decay (blue), faster/steeper than Co (red)
   4: begin
      cutoff_values = fltarr(n_elements(objectlist))
      for i = 0, n_elements(objectlist)-1 do begin
         spot = WHERE((lc_object EQ objectlist[i]) AND (filt EQ 'V'))
         if spot[0] NE -1 then cutoff_values[i] = slope[spot[0]]
      endfor
      cutoff = .94;0.97
      cutoff_param = 'Vslope'
   end
   ; R-band/Unfiltered late-time LC decline rate: slower/shallower than or equal to Co decay (blue), faster/steeper than Co (red)
   5: begin
      cutoff_values = fltarr(n_elements(objectlist))
      for i = 0, n_elements(objectlist)-1 do begin
         spot = WHERE((lc_object EQ objectlist[i]) AND ((filt EQ 'R') OR (filt EQ 'Unf')))
         if spot[0] NE -1 then cutoff_values[i] = slope[spot[0]]
      endfor
      cutoff = .93;0.97
      cutoff_param = 'Rslope'
   end
   ; I-band late-time LC decline rate: slower/shallower than or equal to Co decay (blue), faster/steeper than Co (red)
   6: begin
      cutoff_values = fltarr(n_elements(objectlist))
      for i = 0, n_elements(objectlist)-1 do begin
         spot = WHERE((lc_object EQ objectlist[i]) AND (filt EQ 'I'))
         if spot[0] NE -1 then cutoff_values[i] = slope[spot[0]]
      endfor
      cutoff = 1.0;0.97
      cutoff_param = 'Islope'
   end
endcase



; define some basics based on which spectral range we're viewing
case float(choice) of

   ; each spectral range has:
   ;     lines - array of the wavelengths of (possible) lines in the range
   ;     feature - array of which feature each line is part of
   ;     dominant - array of wavelengths of dominant line in each feature
   ;                (n_elements(dominant) == max(feature)+1
   ;     minl,maxl - a min and max lambda for the range
   1: begin
      ;        Hgamma-weak, Mg I]
      lines = [4341.,       4571.]
      line_string = ['H!9g!7', 'Mg I] 4571']
      feature = [0, 1]
      dominant = [4341., 4571.]
      feature_string = ['H!9g!7', 'Mg I] 4571']
      minl = 4250.
      maxl = 4800.
   end
   2: begin
      ;        Hbeta, Fe II
      lines = [4861., 5018.]
      line_string = ['H!9b!7', 'Fe II 5018']
      feature = [0, 1]
      dominant = [4861., 5018.]
      feature_string = ['H!9b!7', 'Fe II 5018']
      minl = 4820.
      maxl = 5500.
   end
   3: begin
      ;        Fe II (Maguire), Na I D-strong-blended doublet-maybe blended with He I 5876
      lines = [5527.,           5892.]
      line_string = ['Fe II 5527', 'Na I D']
      feature = [0, 1]
      dominant = [5527., 5892.]
      feature_string = ['Fe II 5527', 'Na I D']
      minl = 5420.
      maxl = 6100.
   end
   4: begin
      ;        [O I]-strong, [O I]-sometimes blended with [O I]
      lines = [6300.,        6364.]
      line_string = ['[O I] 6300', '[O I] 6364']
      feature = [0, 0]
      dominant = [6300.]
      feature_string = ['[O I] 6300,6364']
      minl = 6100.
      maxl = 6490.
   end
   5: begin
      ;        Halpha-strong
      lines = [6563.]
      line_string = ['H!9a!7']
      feature = [0]
      dominant = [6563.]
      feature_string = ['H!9a!7']
      minl = 6400.
      maxl = 6700.
   end
   6: begin
      ;        He I,  He I
      lines = [6678., 7065.]
      line_string = ['He I 6678', 'He I 7065']
      feature = [0, 1]
      dominant = [6678., 7065.]
      feature_string = ['He I 6678', 'He I 7065']
      minl = 6600.
      maxl = 7150.
   end
   7: begin
      ;        [Fe II]-usually strong/[Fe II]-sometimes blended with [Fe II], [Ca II]-strong, [Ca II]-sometimes blended with [Ca II]
      lines = [7155.,                                                         7291.,          7324.]
      line_string = ['[Fe II] 7155,7172 ', '[Ca II] 7291', '[Ca II] 7324']
      feature = [0, 1, 1]
      dominant = [7155., 7291.]
      feature_string = ['[Fe II] 7155,7172 ', '[Ca II] 7291,7324']
      minl = 7100.
      maxl = 7430.
   end
   8: begin
      ;       ??????, O I-blended triplet-sometimes blended with stronger 7700 feature
      lines = [7700., 7774.]
      line_string = ['7700?', 'O I 7774']
      feature = [0, 0]
      dominant = [7700.]
      feature_string = ['7700?', 'O I 7774']
      minl = 7650.
      maxl = 7900.
   end
   9: begin
      ;        O I-blended triplet-usually weak, Ca II-usually blended with Ca II, Ca II-strong, Ca II-strong
      lines = [8446.,                            8498.,                            8542.,        8662.]
      line_string = ['O I 8446', 'Ca II 8498', 'Ca II 8542', 'Ca II 8662']
      feature = [0, 1, 1, 2]
      dominant = [8446., 8542., 8662.]
      feature_string = ['O I 8446', 'Ca II 8498,8542', 'Ca II 8662']
      minl = 8400.
      maxl = 8780.
   end
endcase



; go through each feature
iii = 0
for j = iii, n_elements(dominant)-1 do begin

   ; get list of good fits to the current feature
   cut = WHERE((feature_num EQ j) AND (shape NE 0))


   ; spline params
   ;
   ; total flux (spline) vs. t
   ;; al_helper, disc_age, f_tot, f_tot_err, 'f_tot', $
   ;;            'Rest-Frame Days Since Discovery', 'Flux of '+feature_string[j]+' (10!U-15!N erg s!U-1!N cm!U-2!N)', $
   ;;            choice, open_plots, connect, error

   ; total luminosity (spline) vs. t
   al_helper, disc_age, l_tot, l_tot_err, 'l_tot', $
              'Rest-Frame Days Since Discovery', 'Luminosity of '+feature_string[j]+' (erg s!U-1!N)', $
              choice, open_plots, connect, error
   
   ; total luminosity (spline) relative to Co decay power vs. t
   ; only for [OI] doublet, equation from Jerkstrand15b
   if choice EQ '4' then begin
      m_nickel = 0.03 ; 0.002, 0.03, 0.075 in M_Sun
      lscaled = l_tot / 1.06d42 / (exp(-1.*disc_age/111.4) - exp(-1.*disc_age/8.8)) * (0.075/m_nickel)
      lscaled_err = l_tot_err / 1.06d42 / (exp(-1.*disc_age/111.4) - exp(-1.*disc_age/8.8)) * (0.075/m_nickel)
      ; cut the oldest 04dj spectrum cuz its lscaled is huge
      spot = WHERE(disc_age GT 1000)
      lscaled[spot] = 0.
      al_helper, disc_age, lscaled, lscaled_err, 'l_tot_scaled', $
                 'Rest-Frame Days Since Discovery', 'Luminosity of '+feature_string[j]+' (relative to Co decay?)', $
                 choice, open_plots, connect, error
   endif

   ; peak flux (spline) vs. t
   ;; al_helper, disc_age, spline_f_peak, spline_f_peak_err, 'f_pk', $
   ;;            'Rest-Frame Days Since Discovery', 'Flux!Dpeak!N of '+feature_string[j]+' (10!U-15!N erg s!U-1!N cm!U-2!N)', $
   ;;            choice, open_plots, connect, error

   ; luminosity of peak (spline) vs. t
   al_helper, disc_age, spline_l_peak, spline_l_peak_err, 'l_pk', $
              'Rest-Frame Days Since Discovery', 'Luminosity!Dpeak!N of '+feature_string[j]+' (erg s!U-1!N)', $
              choice, open_plots, connect, error

   ; vel of peak (spline) vs. t
   al_helper, disc_age, spline_v, spline_v_err, 'v_pk', $
              'Rest-Frame Days Since Discovery', 'Velocity!Dpeak!N of '+feature_string[j]+' (km s!U-1!N)', $
              choice, open_plots, connect, error


   ; Gaussian params
   ;
   ; initialize feature counter
   feature_counter = 0
   ; go through each feature
   for k = 0, n_elements(feature)-1 do begin

      ; see if current feature is part of the current dominant feature
      if feature[k] EQ j then begin

         ; see which feature this is in the current dominant feature
         case feature_counter of

            0: begin
               ; HWHM (Gaussian) vs. t
               al_helper, disc_age, gauss_1_hwhm, gauss_1_hwhm_err, 'HWHM_0', $
                          'Rest-Frame Days Since Discovery', 'HWHM of '+line_string[k]+' (km s!U-1!N)', $
                          choice, open_plots, connect, error

               ; HWZI (Gaussian) vs. t
               ;; al_helper, disc_age, gauss_1_hwzi, gauss_1_hwzi_err, 'HWZI_0', $
               ;;            'Rest-Frame Days Since Discovery', 'HWZI of '+line_string[k]+' (km s!U-1!N)', $
               ;;            choice, open_plots, connect, error

               ; increment feature counter
               feature_counter++
            end

            1: begin
               ; HWHM (Gaussian) vs. t
               al_helper, disc_age, gauss_2_hwhm, gauss_2_hwhm_err, 'HWHM_1', $
                          'Rest-Frame Days Since Discovery', 'HWHM of '+line_string[k]+' (km s!U-1!N)', $
                          choice, open_plots, connect, error

               ; HWZI (Gaussian) vs. t
               ;; al_helper, disc_age, gauss_2_hwzi, gauss_2_hwzi_err, 'HWZI_1', $
               ;;            'Rest-Frame Days Since Discovery', 'HWZI of '+line_string[k]+' (km s!U-1!N)', $
               ;;            choice, open_plots, connect, error

               ; increment feature counter
               feature_counter++
            end

            2: begin
               ; HWHM (Gaussian) vs. t
               al_helper, disc_age, gauss_3_hwhm, gauss_3_hwhm_err, 'HWHM_2', $
                          'Rest-Frame Days Since Discovery', 'HWHM of '+line_string[k]+' (km s!U-1!N)', $
                          choice, open_plots, connect, error

               ; HWZI (Gaussian) vs. t
               ;; al_helper, disc_age, gauss_3_hwzi, gauss_3_hwzi_err, 'HWZI_2', $
               ;;            'Rest-Frame Days Since Discovery', 'HWZI of '+line_string[k]+' (km s!U-1!N)', $
               ;;            choice, open_plots, connect, error

               ; increment feature counter
               feature_counter++
            end
            3: begin
               ; HWHM (Gaussian) vs. t
               al_helper, disc_age, gauss_4_hwhm, gauss_4_hwhm_err, 'HWHM_3', $
                          'Rest-Frame Days Since Discovery', 'HWHM of '+line_string[k]+' (km s!U-1!N)', $
                          choice, open_plots, connect, error

               ; HWZI (Gaussian) vs. t
               ;; al_helper, disc_age, gauss_4_hwzi, gauss_4_hwzi_err, 'HWZI_3', $
               ;;            'Rest-Frame Days Since Discovery', 'HWZI of '+line_string[k]+' (km s!U-1!N)', $
               ;;            choice, open_plots, connect, error

               ; increment feature counter
               feature_counter++
            end
         endcase
      endif
   endfor

endfor

end
