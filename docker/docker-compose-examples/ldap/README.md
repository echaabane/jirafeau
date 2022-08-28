# Docker-compose example with LDAP

This example help users (and developpers) to setup a LDAP deployment with Jirafeau.

# Build Jirafeau's image

You can skip this step if you are not developping Jirafeau.

In Jirafeau's project folder:
```
docker build . -t mojo42/jirafeau:dev
```

# Customize docker-compose.yml

Open [docker-compose.yml](docker-compose.yml) file and tweak it as needed.
You can change Jirafeau's image to an official release if you are not developping Jirafeau.

# Run docker compose

```
docker-compose up -d
docker-compose logs -f
```

# Testing

You can now connect to [127.0.0.1:8080](http://127.0.0.1:8080/) to access Jirafeau instance and [127.0.0.1:8090](http://127.0.0.1:8090/) to access PHP LDAP Admin.

You can login on PHP LDAP admin with those default credentials:
- login DN: `cn=admin,dc=jirafeau,dc=net`
- Password: `admin`

Once connected on PHP LDAP Admin, you can import [bootstrap.jirafeau.ldif](bootstrap.jirafeau.ldif) to inject a test user.
Once imported, you should be able to login on Jirafeau with those credentials:
- Login: `jerome`
- Password: `password`

