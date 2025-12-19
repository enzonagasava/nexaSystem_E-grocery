CREATE DATABASE IF NOT EXISTS familiaMogi_credentials;
CREATE DATABASE IF NOT EXISTS familiaMogi_content;

GRANT ALL PRIVILEGES ON familiaMogi_credentials.* TO 'familiaMogi'@'%';
GRANT ALL PRIVILEGES ON familiaMogi_content.* TO 'familiaMogi'@'%';

FLUSH PRIVILEGES;
