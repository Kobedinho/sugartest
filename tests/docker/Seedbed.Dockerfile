# Your installation or use of this SugarCRM file is subject to the applicable
# terms available at
# http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
# If you do not agree to all of the applicable terms or do not have the
# authority to bind the entity as an authorized representative, then do not
# install or use this SugarCRM file.
#
# Copyright (C) SugarCRM Inc. All rights reserved.

FROM registry.sugarcrm.net/engineering/node-selenium:latest
MAINTAINER Engineering Automation "engineering-automation@sugarcrm.com"

# Add multimedia repository, required to install ffmpeg
RUN \
    echo "deb http://www.deb-multimedia.org jessie main non-free" >> /etc/apt/sources.list && \
    echo "deb-src http://www.deb-multimedia.org jessie main non-free" >> /etc/apt/sources.list && \
    apt-get update && \
    apt-get install -y --force-yes deb-multimedia-keyring && \
    apt-get update

# Install Seedbed-specific external dependencies
RUN apt-get install -y --force-yes --no-install-recommends \
    graphicsmagick ffmpeg

# Cleanup
RUN apt-get autoremove -y && apt-get clean && \
    rm -Rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

ADD Seedbed.Entrypoint.sh /Seedbed.Entrypoint.sh

# Default command to run when container starts:
ENTRYPOINT ["/Seedbed.Entrypoint.sh"]
