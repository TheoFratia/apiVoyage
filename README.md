vérifier si il y a clé privé et public sinon régénérer 
clé privé :
openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
clé public :
openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout
