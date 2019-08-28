**Pimcore FileTransferBundle**

Install:

    composer require youwe/filetransferbundle

Enable extension in Pimcore, copy the configuration lines from 
the plugins /Resources/config/pimcore/config.yml file to the
projects /app/config/config.yml and change them to match your
configurations.

Then use this command line command to transfer a file:

    bin/console transfer:file myfileserver /tmp/test.txt /home/myusername/test.txt

Any errors will be logged to the Application Logger. If no feedback
is shown, the file transfer went succesfully.

Example of downloading from remote sftp
```
./bin/console transfer:file -m get --ignore=Archive <NAME OF SERVICE> /path/on/server /localpath
```
