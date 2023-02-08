# The Enphase Prometheus docker app

## getting the enlighten token for your envoy

Because the auth for enphase has been been changed to enlighten to tokens with only a 12 hour lifetime
this docker application will do the following
- log into your enlighten account, then
- create a token, then
- use that token to authenticate with your envoy, then
- extract the data from the envoy and present it in a /metrics path

all of the actions above are cached for about 6 hours, so it wont spam enlighten with logins and token creations.

lastly and most importantly once a session is established with your envoy this will retreive data STRAIGHT FROM YOUR ENVOY,
this means no ridiculous api fees form enlighten!

## running the container

the container can be run like so, although I would suggest you store you token in a token file and
run the environment with -e "ENPHASE_TOKEN=$(cat .tokenFile)" or with an environment file

```
docker run\
 -p 8000:8000\
 -e ENPHASE_HOST={ip_address_of_envoy}\
 -e ENPHASE_EMAIL={enlighted_email}\
 -e ENPHASE_PASSWORD={enlighten_password}\
 -e ENPHASE_SITE={enlighten_site_name}\
 -it mooseyman1988/enphase-prometheus
```

## running in docker compose

```
version: "3.6"
services:
  enphase_prometheus:
    image: mooseyman1988/enphase-prometheus
    container_name: enphase_exporter
    ports:
      - 8000:8000
    environment:
      - ENPHASE_HOST={ip_address_of_envoy}
      - ENPHASE_EMAIL={enlighted_email}
      - ENPHASE_PASSWORD={enlighted_email}
      - ENPHASE_SITE={enlighten_site_name} #must be exact
    # or with an env file
    #env_file:
    #  - .enphase.env
    restart: unless-stopped
```


