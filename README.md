# bellsystem
Pi based PHP service that queries Google Calendar events



# Setup Pi
sudo raspi-config
Setup ip/static/etc
sudo apt update
sudo apt install cron

# Install Apache, PHP (with cli) and Composer
```
sudo apt install apache2 -y
```

Go to ip address, see if apache start page works, then get rid of page.
```
sudo rm /var/www/html/index.html
```

```
sudo apt install php -y
sudo apt install php-cli unzip curl -y
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```
Verify composer is installed
```
composer --version
```
Install api client for google and cron scheduler
```
composer require google/apiclient
composer require peppeocchi/php-cron-scheduler

```


# Create Service
```
sudo nano /etc/systemd/system/php-audio.service
```
```
[Unit]
Description=Bell Schedule PHP Script
After=network.target

[Service]
Type=oneshot
ExecStart=/usr/bin/php /var/www/html/execute.php
User=pi
Group=www-data

[Install]
WantedBy=multi-user.target
```
Then enable service:
```
sudo systemctl enable php-audio.service
sudo systemctl start php-audio.service
sudo systemctl status php-audio.service
```

# Reboot Scheduler
```
sudo nano /etc/systemd/system/reboot-daily.service
```
```
[Unit]
Description=Reboot the system daily at 6 AM

[Service]
Type=simple
ExecStart=/usr/bin/systemctl --force reboot
```
Then set timer
```
sudo nano /etc/systemd/system/reboot-daily.timer
```
```
[Unit]
Description=Daily timer to reboot the system at 6 AM

[Timer]
OnCalendar=*-*-* 06:00:00

[Install]
WantedBy=timers.target
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
