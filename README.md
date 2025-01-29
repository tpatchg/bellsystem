# bellsystem
debian based PHP service that queries Google Calendar events and schedules "at" commands to play the files



# Setup Debian
Graphical Install does most of the work.  Just select ssh server and base components.  Web-server installs too many modules.
Log in as root to install required packages.
```
su -
apt install at -y
```

# Install Apache, PHP (with cli) and Composer
```
apt install apache2 php-cli php-fpm -y
a2enmod proxy_fcgi setenvif
a2enconf php8.2-fpm
systemctl restart apache2
```

Go to ip address, see if apache start page works, then get rid of page.
```
rm /var/www/html/index.html
```

Install Composer, execute script from https://getcomposer.org/download/
```
mv composer.phar /usr/local/bin/composer
```
Verify composer is installed, then change site ownership to user, and exit root.
```
composer --version
chown -R user:root /var/www/html
exit
```
Install api client for google in the web directory
```
cd /var/www/html
composer require google/apiclient
```


# Get control, status, and scheduler
Get the files above into the /var/www/html directory.
```

```


# Optional Video output status screen
```
sudo apt install --no-install-recommends xserver-xorg x11-xserver-utils xinit openbox cec-utils xdotool chromium-br>
```
Setup autostart file, first upload system_startup.mp3 into /etc/xdg/openbox/
```
sudo nano /etc/xdg/openbox/autostart
```
```
# Disable screen saver / screen blanking / power management
xset s off
xset s noblank
xset -dpms

#Allow quitting the X server with CTRL-ATL-Backspace
setxkbmap -option terminate:ctrl_alt_bksp

#Start Chromium browser in kiosk mode
sed -i 's/"exited_cleanly":false/"exited_cleanly":true/' ~/.config/chromium/'Local State'
sed -i 's/"exited_cleanly":false/"exited_cleanly":true/; s/"exit_type":"[^"]+"/"exit_type":"Normal"/' ~/.config/chromium/Default/Preferences
chromium-browser --disable-infobars --kiosk 'http://localhost/' &

#Play startup noise
play /etc/xdg/openbox/system_startup.mp3
```
