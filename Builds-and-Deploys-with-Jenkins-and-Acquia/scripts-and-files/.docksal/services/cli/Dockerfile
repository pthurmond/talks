# Use a stock Docksal image as the base
FROM docksal/cli:2.2-php7.2

# Install addtional apt packages
RUN apt-get update && apt-get -y --no-install-recommends install \
    build-essential \
    bsdtar \
    unzip \
    # Cleanup
    && apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Copy additional config files
#COPY ssh.conf /opt/cli/ssh.conf

# Inject additional SSH settings
#RUN \
#    cat /opt/cli/ssh.conf >> /home/docker/.ssh/config && \
#    cat /home/docker/.ssh/config

# All further commands will be performed as the docker user.
USER docker

# Install additional global npm dependencies
RUN \
    # Initialize nvm environment
    . $NVM_DIR/nvm.sh 

# IMPORTANT! Switching back to the root user as the last instruction.
USER root