#!/bin/bash

case "$1" in
  "exec")
    shift: eval "$@";;
  "symfony" | "git" | "composer")
    eval "$@";;
  *)
    [ $# -eq 0 ] && set -- "-a"; php "$@";;
esac
exit $?