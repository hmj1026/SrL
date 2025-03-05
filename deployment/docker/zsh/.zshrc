export ZSH="/root/.oh-my-zsh"

ZSH_THEME="Soliah"

plugins=(git git-flow git-flow-avh gitfast gitignore history history-substring-search fasd aws z laravel5 composer)

source $ZSH/oh-my-zsh.sh

export NVM_DIR="$HOME/.nvm"
[ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh"
[ -s "$NVM_DIR/bash_completion" ] && \. "$NVM_DIR/bash_completion" 
