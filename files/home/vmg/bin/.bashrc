# ~/.bashrc: executed by bash(1) for non-login shells.

export HISTCONTROL=ignoreboth
#export PS1='\h:\w\$ '
umask 022
case $TERM in
        Eterm*)
        TITLEBAR='\[\033]0; \u@\h : \w \007\]' ;;
                screen)
                TITLEBAR='\[\033]0; \u@\h : \w \007\]' ;;
        xterm*)
        TITLEBAR='\[\033]0; \u@\h : \w \007\]' ;;
                screen)
                TITLEBAR='\[\033]0; \u@\h : \w \007\]' ;;
        *)
        TITLEBAR='' ;;
esac
export PS1="${TITLEBAR}\n\t \d\n[\u@\h \W]> "


# You may uncomment the following lines if you want `ls' to be colorized:
# export LS_OPTIONS='--color=auto'
# eval "`dircolors`"
# alias ls='ls $LS_OPTIONS'
# alias ll='ls $LS_OPTIONS -l'
# alias l='ls $LS_OPTIONS -lA'
#
# Some more alias to avoid making mistakes:
# alias rm='rm -i'
# alias cp='cp -i'
# alias mv='mv -i'
# 
# unset autoindent -> :setl noai nocin nosi inde=
alias ls='ls -AF'
