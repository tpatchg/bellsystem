# bellsystem
Pi based PHP service that queries Google Calendar events



# Setup Pi
sudo raspi-config
Setup ip/static/etc

# Install Apache, PHP (with cli) and Composer
sudo apt update
sudo apt install apache2 -y

Go to ip address, see if apache start page works, then get rid of page.
sudo rm /var/www/html/index.html

sudo apt install php -y
sudo apt install php-cli unzip curl -y
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
composer --version
composer require google/apiclient


# Create Service
sudo nano /etc/systemd/system/php-audio.service
sudo systemctl enable php-audio.service
sudo systemctl start php-audio.service
sudo systemctl status php-audio.service

# Optional Video output status screen
sudo apt install --no-install-recommends xserver-xorg x11-xserver-utils xinit openbox cec-utils xdotool chromium-br>
sudo nano /etc/xdg/openbox/autostart
