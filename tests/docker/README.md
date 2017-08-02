# Building Mango Docker Images

These instructions assume you are in the sugarcrm/tests/docker directory.

## Modifying Docker Entrypoint Files

As a friendly reminder, you should know that we copy each entrypoint script
into its Docker image root so that we can check for the presence of a /sugarcrm
mount. Whenever you make changes to an entrypoint script your changes won't be
utilized until you rebuild that Docker image. As another friendly reminder, if
you haven't changed anything inside the Dockerfile since the last time you
built it, the Dockerfile won't automatically rebuild because Docker has no way
to detect that you altered the contents of a script that's included in the
Docker image itself. To forcefully rebuild the docker image after making
changes to the entrypoint script or any other files included in the image, run
docker build with the --no-cache argument.

## Engineering Node Image

This image installs yarn, gulp, and a few other utilities used in CI. Our *latest* tag should always point to the latest *LTS release* of the [official node docker image](https://hub.docker.com/_/node/).
```
docker build -f Node.Dockerfile -t registry.sugarcrm.net/engineering/node:latest .
```

## Engineering Node Selenium Image

This image installs everything required to run Selenium on top of our Node image.
```
docker build -f Node.Selenium.Dockerfile -t registry.sugarcrm.net/engineering/node-selenium:latest .
```

## Thorn Image

This image installs everything required for Thorn to run on top of our Node image.
```
docker build -f Thorn.Dockerfile -t registry.sugarcrm.net/thorn/thorn:latest .
```

## Seedbed Image

This image installs everything required for Seedbed to run on top of our Node Selenium image.
```
docker build -f Seedbed.Dockerfile -t registry.sugarcrm.net/seedbed/seedbed:latest .
```

# Pushing Images

Just push them to our internal repository:
```
docker push registry.sugarcrm.net/namespace/image_name:tag
```
