#!/bin/bash

# Instala Node.js 20 LTS desde NodeSource
curl -fsSL https://rpm.nodesource.com/setup_20.x | bash -
yum install -y nodejs

# Verifica que node esté disponible
node -v
npm -v
