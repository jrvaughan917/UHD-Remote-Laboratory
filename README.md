# UHD-Remote-Laboratory

This is the main page for a website which I created for the University of Houston-Downtown. For security purposes, sensitive information has been removed or changed. I only included this one page, to make it more difficult for others to clone or pirate the website without permission. The website is currently up and running at www.uhdremotelab.com, but it is only for UHD students and faculty use. The Remote Laboratory is a remote platform which allows students to remotely operate a vibration experiment machine from their own browser. The experiment machine is controlled via LabVIEW, and client machines must have the LabVIEW Runtime Engine installed to operate the machine remotely. 

This web page offers account creation with email verification, password salting and hashing, time slot reservation, experiment file upload and download, instructions and resources on how to use the machine, and more. It was written in HTML, CSS, JavaScript, and PHP, and interfaces with a MySQL database for data storage. It also uses jQuery objects and Bootstrap styling. It uses MySQLi escape strings to make SQL injection more difficult, and also uses regex both in JavaScript and on the host in PHP to validate input such as emails.

I also wrote a 42-page documentation for the project, for faculty and student use. I may upload this document here at a later date.
