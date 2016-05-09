IDL (Interactive Data Language) is a programming language used for data analysis that is popular with astronomers. 
It is similar in syntax, style, and function as R and MATLAB. 
It has been my primary coding and analysis language throughout my astronomy career.

Programs in this folder include:
- analyze_lines.pro - Reads in numerical values of measurements from observations of exploding stars (aka supernova) along with associated metadata for each supernova. Optional inputs include details on how resulting plots should appear and which subset of measurements should be analyzed. Some additional values are then calculated from the ingested measurements and the user chooses which parameters to compare the measurements to. Each type of measurement is then plotted and basic analysis is performed (via the al_helper.pro script, see below).

- al_helper.pro - Takes as input the measurements from the supernova data, their metadata, and various plotting/graphical flags and options from analyze_lines.pro (see above). A plot of the measurements vs. time is produced as .PS and .PDF files. It also prints the Pearson correlation coefficient of the measurements (to see how strongly linearly correlated they are in time). In addition, it calculates the mean and median measurement for each supernova and then breaks these measurements into two populations given a cutoff value.
Using the two populations, it then performs a few Kolmogorov-Smirnov tests to see how likely it is that they come from different parent populations.
