export REGION=us-central1
export SERVICE_NAME=trandview2020
export PROJECT_ID=projecttrandingview2021
export INSTANCE_NAME=resource001

gcloud config set project $PROJECT_ID

gcloud builds submit \
  --project ${PROJECT_ID} \
  --config .cloudbuild/build-migrate-deploy.yaml \
  --substitutions _APP_ENV=production,_APP_DEBUG=false,_SERVICE=${SERVICE_NAME},_REGION=${REGION},_INSTANCE_NAME=${INSTANCE_NAME}