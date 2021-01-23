export REGION=us-central1
export SERVICE_NAME=trandview2021
export PROJECT_ID=trandingviewsignal

docker rm -f ${SERVICE_NAME}
docker build -f Dockerfile -t us.gcr.io/${PROJECT_ID}/${SERVICE_NAME} .
docker run -d -v `pwd`:/app -e PORT=80 --env-file=.env -p 80:80 --name=${SERVICE_NAME} us.gcr.io/${PROJECT_ID}/${SERVICE_NAME}
docker rmi $(docker images --filter "dangling=true" -q --no-trunc)
docker exec -it ${SERVICE_NAME} /bin/sh