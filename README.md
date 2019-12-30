# Local Environment
> Using Docker for our local environment

## Requirements

1. Having [Docker installed](https://www.docker.com/products/docker-desktop) (you will need to create a Hub account)
2. Having [Git installed](https://git-scm.com/downloads)

## Installation

1. Clone this repository into your projects folder using the `git clone` command

## Instructions

1. After cloning the project, open your terminal and access the root folder using the `cd /path/to/the/folder` command.
2. To start the local environment, run the command `make run`. For Windows users, run the command `docker-compose -d up`.

**Note:** The first time you run this command it will take some time because it will download all the required images from the Hub.

At this point, if you run the command `docker ps` you should see a total of 4 containers running:

```
pw_local_env-nginx
pw_local_env-admin
pw_local_env-php
pw_local_env-db
```

The application should be running in the 8030 port of your local machine but, before trying it, lets add one entry to your **hosts** file. For OSX users, this file should located at `/etc/hosts`. For Windows users, you can check [this guide](https://www.howtogeek.com/howto/27350/beginner-geek-how-to-edit-your-hosts-file/). Edit the file and add the following entry:

```
127.0.0.1 pw.test
```

At this point, you should be able to access to the application by visiting the following address in your browser [http://pw.test:8030/](http://pw.test:8030/).

## QA

1. How to list all the running containers

Use the `docker ps` command. Use the -a flag to list also the stopped ones.

2. How to list all the docker images that I have installed

Run the command `docker images`

3. How to remove all the images that are no longer used

Run the command `docker image prune`

4. How to check the logs of an specific container

Run the command `docker ps` and copy the id of the container that you want to debug.

Now, run the command `docker logs --follow container_id`.

5. How to _ssh_ into an specific container

Run the command `docker ps` and copy the id of the container that you want to debug.

Now, run the command `docker exec -it container_id bash`.

**Note:** If you are using the alpine version of image, you need to use _ash_ instead of _bash_.

6. Where can I find more docker images to use them

You can check the [Docker Hub](https://hub.docker.com/).

