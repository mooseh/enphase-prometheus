# The Enphase Prometheus docker app

## getting the enlighten token for your envoy

Because the auth for enphase has been been changed to enlighten
this application will extract the session information from the envoy using the enlighten token,
this will be cached for 1 hour and will re-attempt on authentication failure.

## running the container

the container can be run like so, although I would suggest you store you token in a token file and
run the environment with -e "ENPHASE_TOKEN=$(cat .tokenFile)" or with an environment file

`
docker run\
 -p 8000:8000\
 -e ENPHASE_HOST={ip_address_of_envoy}\
 -e ENPHASE_TOKEN={enlighten_token}\
 -it mooseyman1988/enphase-prometheus
`

## running in docker compose

`
version: "3.6"
services:
  enphase_prometheus:
    container_name: enphase_exporter
    ports:
      - 8000:8000
    environment:
      - ENPHASE_HOST={ip_address_of_envoy}
      - ENPHASE_TOKEN={my_enlighten_token}
    # or with an env file
    env_file:
      - .enphase.env
    restart: unless-stopped
`


