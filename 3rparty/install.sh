PROGRESS_FILE=/tmp/dependancy_letsencrypt_in_progress
if [ ! -z $1 ]; then
	PROGRESS_FILE=$1
fi
touch ${PROGRESS_FILE}
echo 0 > ${PROGRESS_FILE}
echo "********************************************************"
echo "*             Installation des dépendances             *"
echo "********************************************************"
apt-get install -y python-certbot-apache
echo 50 > ${PROGRESS_FILE}
echo 65 > ${PROGRESS_FILE}
echo 75 > ${PROGRESS_FILE}
echo 85 > ${PROGRESS_FILE}
echo 100 > ${PROGRESS_FILE}
echo "********************************************************"
echo "*             Installation terminée                    *"
echo "********************************************************"
rm ${PROGRESS_FILE}
#a2enmod http2
#echo http2 enabled