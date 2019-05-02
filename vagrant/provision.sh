echo '-- Adding ppa:ondrej/php --';
add-apt-repository ppa:ondrej/php
apt-get update
echo '-- Successful --';

echo '-- Installing PHP and extensions --';
apt-get install -y zip unzip composer php7.3-zip php7.3-curl php7.3-mbstring php7.3-gmp php7.3-xml
echo '-- Successful --';

