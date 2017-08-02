# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

FROM registry.sugarcrm.net/engineering/node:latest
MAINTAINER Engineering Automation "engineering-automation@sugarcrm.com"

ENV GEOMETRY 1920x1080x24
ENV DISPLAY :0
ENV DBUS_SESSION_BUS_ADDRESS /dev/null

# Prevents timezone message in Sugar instance (it was covering page elements)
ENV TZ America/Los_Angeles

RUN apt-get update

# Install virtual frame buffer for graphical support
RUN apt-get install -y --force-yes --no-install-recommends \
    xvfb

# Install VNC server
RUN apt-get install -y --force-yes --no-install-recommends \
    x11vnc

RUN \
  mkdir -p ~/.vnc && \
  x11vnc -storepasswd '' ~/.vnc/passwd

# Install Java 8, required for Selenium
RUN \
    echo "deb http://ppa.launchpad.net/webupd8team/java/ubuntu trusty main" > /etc/apt/sources.list.d/java-8-debian.list && \
    echo "deb-src http://ppa.launchpad.net/webupd8team/java/ubuntu trusty main" >> /etc/apt/sources.list.d/java-8-debian.list && \
    apt-key adv --keyserver keyserver.ubuntu.com --recv-keys EEA14886 && \
    apt-get update && \
    echo oracle-java8-installer shared/accepted-oracle-license-v1-1 select true | /usr/bin/debconf-set-selections && \
    apt-get install -y --no-install-recommends oracle-java8-installer

# Install Google Chrome
RUN \
    wget -q -O - https://dl-ssl.google.com/linux/linux_signing_key.pub | apt-key add - && \
    echo "deb http://dl.google.com/linux/chrome/deb/ stable main" > /etc/apt/sources.list.d/google.list && \
    apt-get update -qqy && \
    apt-get install -qqy google-chrome-stable

# Cleanup
RUN apt-get autoremove -y && apt-get clean && \
    rm -Rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

EXPOSE 5900

# To properly use this Dockerfile your entrypoint script must run xvfb and
# x11vnc, like this:
#
# Xvfb "${DISPLAY}" -screen 0 "${GEOMETRY}" &
# x11vnc -display "${DISPLAY}" -bg -nopw -xkb -usepw -shared -repeat -loop -forever > /usr/local/x11vnc.log 2>&1 &
