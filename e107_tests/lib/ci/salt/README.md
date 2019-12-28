# e107 Local Test Container Setup

1. Launch a development container:

   ```
   lxc launch -s local images:ubuntu/focal e107-dev
   ```

2. Push your public SSH key:

   ```
   lxc file push --uid 0 --gid 0 -pv ~/.ssh/id_rsa.pub e107-dev/root/.ssh/authorized_keys
   ```

3. Install OpenSSH Server:

   ```
   lxc exec e107-dev -- apt install -y openssh-server
   ```

4. Note the IP of the container:

   ```
   E107_DEV_HOST=$(lxc exec e107-dev -- hostname -I | cut -d' ' -f1)
   ```

5. Generate the [Salt SSH](https://docs.saltstack.com/en/latest/topics/ssh/) [roster](https://docs.saltstack.com/en/latest/topics/ssh/roster.html):

   ```
   echo "e107-dev: $E107_DEV_HOST" | tee roster
   ```

6. Configure `e107_tests/config.yml` based on `e107_tests/config.sample.yml` (from the root of this repository).

   For all tests:

   > Set `db.dbname`, `db.user`, and `db.password` to what you want the container configuration to have.
   >
   > Set `db.host` to the value of `$E107_DEV_HOST`.
   
   For acceptance tests:
   
   > Set `deployer` to `sftp`.
   >
   > Set `fs.host` to the value of `$E107_DEV_HOST`.
   >
   > Set `fs.user` to `www-data`.
   >
   > Set `fs.password` to any password you want the user to have.
   >
   > Set `fs.path` to `/var/www/html/e107/`.
   >
   > Set `url` to the output of `echo "http://$E107_DEV_HOST/e107/"`

7. Apply the container configuration:
   ```
   salt-ssh 'e107-dev' --state-output=changes state.apply e107-dev
   ```