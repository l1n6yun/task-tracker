#!/bin/bash

if [ ! -f ~/.bashrc ]; then
  echo "alias task-cli='$(pwd)/task-cli.php'" >> ~/.bashrc
  source ~/.bashrc
else
  if ! grep -q "alias test-cli=" ~/.bashrc;  then
    echo "alias task-cli='$(pwd)/task-cli.php'" >> ~/.bashrc
    source ~/.bashrc
  fi
fi
