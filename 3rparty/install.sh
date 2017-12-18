PROGRESS_FILE=/tmp/dependancy_letsencrypt_in_progress
if [ ! -z $1 ]; then
	PROGRESS_FILE=$1
fi
touch ${PROGRESS_FILE}
echo 0 > ${PROGRESS_FILE}
echo "********************************************************"
echo "*             Installation des dépendances             *"
echo "********************************************************"
apt-get install -y lsb-release
version=$(lsb_release -sc)
if [ $version == 'stretch' ]
then
    apt-get install -y python-certbot-apache 2>&1
else 
	apt-get install -y git
	echo 50 > ${PROGRESS_FILE}
	cd /opt
	git clone https://github.com/letsencrypt/letsencrypt
	echo 75 > ${PROGRESS_FILE}
	cd letsencrypt
	./certbot-auto --help
	echo 85 > ${PROGRESS_FILE}
	ln -s /opt/letsencrypt/certbot-auto /usr/local/sbin/certbot
fi
echo 100 > ${PROGRESS_FILE}
echo "********************************************************"
echo "*             Installation terminée                    *"
echo "********************************************************"
rm ${PROGRESS_FILE}
#a2enmod http2
#echo http2 enabled