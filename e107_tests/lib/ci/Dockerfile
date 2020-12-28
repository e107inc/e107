FROM ubuntu:20.04

ENV DEBIAN_FRONTEND noninteractive

RUN apt-get update
RUN apt-get install -y wget gnupg
RUN wget -O - https://repo.saltstack.com/py3/ubuntu/20.04/amd64/latest/SALTSTACK-GPG-KEY.pub | apt-key add -
RUN mkdir -pv /etc/apt/sources.list.d/
RUN echo 'deb http://repo.saltstack.com/py3/ubuntu/20.04/amd64/latest focal main' |\
    tee /etc/apt/sources.list.d/saltstack.list
RUN apt-get update
RUN apt-get install -y systemd-sysv salt-minion openssh-server rsync
RUN systemctl disable salt-minion.service
RUN mkdir -pv /etc/salt/

COPY salt /var/tmp/salt
COPY config.ci.yml /var/tmp/salt/pillars/config-local.sls
RUN rm -fv /var/tmp/salt/pillars/config.sls && touch /var/tmp/salt/pillars/config.sls
RUN rm -fv /var/tmp/salt/pillars/config-sample.sls && touch /var/tmp/salt/pillars/config-sample.sls
RUN cp -fv /var/tmp/salt/master /etc/salt/minion

WORKDIR /var/tmp/salt
RUN salt-call -l debug --id=e107-dev --local state.apply e107-dev
WORKDIR /

VOLUME ["/sys/fs/cgroup"]
ENTRYPOINT ["/usr/sbin/init"]