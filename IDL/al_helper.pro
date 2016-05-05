; does the heavy lifting for analyze_lines.pro (see that code for more info)
PRO al_helper, ages, values, errs, value_string, xstring, ystring, choice, open_plots, connect, error

  COMMON SHARE

  loadct, 13
  ;col=250  red
  ;col=70   blue

  ; start plot
  plotname =  'plots/'+value_string+'_t_'+choice+'_'+strcompress(j,/rem)+'_'+cutoff_param
  if keyword_set(connect) then plotname = plotname+'_connected'
  if keyword_set(error) then plotname = plotname+'_error'
  ps_open, plotname, /ps_font, /color
  device,  /inches, /times, xsize = 10.5, ysize = 8.0, $
           font_size = 12, yoffset = 10.5, xoffset = 0.50

  ; if a luminosity, then use a logarithmic y-axis
  if (value_string EQ 'l_tot') OR (value_string EQ 'l_pk') then begin
     ; set up plot
     plot, [-1e6],[-1e6], ystyle = 3, xstyle = 3, thick = 6, $
           xthick = 6, ythick = 6, charsize = 1.5, $
           xcharsize = 1.5, charthick = 6, ycharsize = 1.5, $
           /ylog, ytickformat = 'logticks_exp', $
           xra=[MIN(ages[cut]),MAX(ages[cut])],yra=[MIN(values[cut]),MAX(values[cut])], $
           xtit=xstring, ytit=ystring
  endif else begin
     ; set up plot
     plot, [-1e6],[-1e6], ystyle = 3, xstyle = 3, thick = 6, $
           xthick = 6, ythick = 6, charsize = 1.5, $
           xcharsize = 1.5, charthick = 6, ycharsize = 1.5, $
           xra=[MIN(ages[cut]),MAX(ages[cut])],yra=[MIN(values[cut]),MAX(values[cut])], $
           xtit=xstring, ytit=ystring
  endelse

  ; initialize list of all objects
  obj_done = ''
  ; initalize lists of mean and median param values
  means = dblarr(n_elements(objectlist))
  medians = dblarr(n_elements(objectlist))
  ; initialize list of KS parameter
  ksparam = fltarr(n_elements(objectlist))
  ; initialize counter for means and medians
  index = 0

  ; go through each object
  for i = 0, n_elements(obj_params)-1 do begin

     ; see if we've done this object already
     if strpos(obj_done, obj_params[i]+',') EQ -1 then begin

        ; if doing a flux or lum
        if ((value_string EQ 'f_tot') OR (value_string EQ 'l_tot') OR  (value_string EQ 'f_pk') OR (value_string EQ 'l_pk')) then begin
           ; get all good fits of this feature of the current object ONLY if it was scaled to concurrent photometry
           cut2 = WHERE((obj_params[i] EQ obj_params) AND (feature_num EQ j) AND (shape NE 0) AND (strpos(filename_params[i],'scaled') NE -1))
        endif else begin
           ; get all good fits of this feature of the current object
           cut2 = WHERE((obj_params[i] EQ obj_params) AND (feature_num EQ j) AND (shape NE 0))
        endelse



        ; get current object's info
        spot = (WHERE(objectlist EQ obj_params[i]))[0]

        ; get mean and median of current object's param values
        if (cut2[0] NE -1) then begin
           means[index] = mean(values[cut2])
           medians[index] = median(values[cut2],/even)
           ksparam[index] = cutoff_values[spot]
        endif
        index++



        ; if connecting points of same object and more than 1 point to connect
        if keyword_set(connect) AND (n_elements(cut2) GT 1) then begin

           ; figure out color based on cutoff param and cutoff value
           col = 0
           if ((cutoff_values[spot] LE cutoff) AND (cutoff_values[spot] NE 0)) then col = 70
           if ((cutoff_values[spot] GT cutoff) AND (cutoff_values[spot] NE 0)) then col = 250

           ; overplot lines connecting individual objects
           oplot,ages[cut2],values[cut2],thick=4,linestyle=0,col=col

        endif

        ; record object as done
        obj_done = obj_done + obj_params[i] + ','

     endif
  endfor



  ; do analysis based on cutoff param and cutoff value
  cuta = WHERE((ksparam GT cutoff) AND (ksparam NE 0) AND (means NE 0))
  cutb = WHERE((ksparam LE cutoff) AND (ksparam NE 0) AND (means NE 0))
  if (n_elements(cuta) LT 4) OR (n_elements(cutb) LT 4) then begin
     prob = 1.000
     prob1 = 1.000
  endif else begin
     kstwo, means[cuta], means[cutb], D, prob
     kstwo, medians[cuta], medians[cutb], D1, prob1
  endelse
  print, ''
  print, 'N('+cutoff_param+' > '+strcompress(cutoff,/rem)+')   '+strcompress(n_elements(cuta),/rem)
  print, 'N('+cutoff_param+' <= '+strcompress(cutoff,/rem)+')  '+strcompress(n_elements(cutb),/rem)
  print, 'mean('+value_string+') '+cutoff_param+' > '+strcompress(cutoff,/rem)+' vs. <= '+strcompress(cutoff,/rem)+'    '+strcompress(prob,/rem)
  print, 'median('+value_string+') '+cutoff_param+' > '+strcompress(cutoff,/rem)+' vs. <= '+strcompress(cutoff,/rem)+' '+strcompress(prob1,/rem)
  print, 'mean/median of mean('+value_string+') '+cutoff_param+' > '+strcompress(cutoff,/rem)+'    '+strcompress(mean(means[cuta]))+' '+strcompress(median(means[cuta],/even))
  print, 'mean/median of median('+value_string+') '+cutoff_param+' > '+strcompress(cutoff,/rem)+'  '+strcompress(mean(medians[cuta]))+' '+strcompress(median(medians[cuta],/even))
  print, 'mean/median of mean('+value_string+') '+cutoff_param+' <= '+strcompress(cutoff,/rem)+'   '+strcompress(mean(means[cutb]))+' '+strcompress(median(means[cutb],/even))
  print, 'mean/median of median('+value_string+') '+cutoff_param+' <= '+strcompress(cutoff,/rem)+' '+strcompress(mean(medians[cutb]))+' '+strcompress(median(medians[cutb],/even))
  print, 'linear Pearson corr coef: ',correlate(ages, values)
  aaa = ''
  if (prob LT 0.07) OR (prob1 LT 0.07) then aaa = get_kbrd(1)
  

  ; close PS file, convert to PDF
  ps_close
  spawn,'ps2pdf '+plotname+'.ps '+plotname+'.pdf'
  ; open the PDF if the keyword is set
  if keyword_set(open_plots) then $
     spawn,'open '+plotname+'.pdf'
  print, ''

end
