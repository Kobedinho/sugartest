# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

FROM node:6.9.4
MAINTAINER Engineering Automation "engineering-automation@sugarcrm.com"

# Set debconf to run non-interactively
RUN echo 'debconf debconf/frontend select Noninteractive' | debconf-set-selections

# Replace shell with bash so we can source files
RUN rm /bin/sh && ln -s /bin/bash /bin/sh

RUN apt-get update

# Update node registry
RUN \
    npm cache clear && \
    npm config set registry https://cache.sugardev.team/repository/npm/

# Install basic build dependencies
RUN apt-get upgrade -y && \
    DEBIAN_FRONTEND=noninteractive && \
    apt-get install -y --force-yes --no-install-recommends \
    apt-transport-https build-essential ca-certificates lsb-release python \
    rlwrap software-properties-common

# Install Yarn
#RUN npm install --global yarn
RUN curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add - && \
    echo "deb https://dl.yarnpkg.com/debian/ stable main" | tee /etc/apt/sources.list.d/yarn.list && \
    apt-get update && apt-get install yarn

# Install gulp CLI
RUN yarn global add gulp-cli

# Install CI-specific goodies
RUN apt-get install -y --force-yes --no-install-recommends \
    vim curl wget git zip unzip

# Cleanup
RUN apt-get autoremove -y && apt-get clean && \
    rm -Rf /var/lib/apt/lists/* /tmp/* /var/tmp/*
