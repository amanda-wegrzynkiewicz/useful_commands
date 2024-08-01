# First SetUp

1. Set user
```bash
git config --global user.email "fra@muga.com"
git config --global user.name "Fra Muga"
```

2. Set aliases
```bash
git config --global alias.s "status"

git config --global alias.lg "log --pretty=format:'%C(green)%h %C(reset)-%C(auto)%d %s %Cgreen(%cr) %C(bold blue)<%an>%Creset' --abbrev-commit --graph --color --date-order --all -20"

git config --global alias.la "log --pretty=format:'%C(green)%h %C(reset)-%C(auto)%d %s %Cgreen(%cr) %C(bold blue)<%an>%Creset' --abbrev-commit --graph --color --date-order --all"
```

3. Rebase from detached HEAD
```bash
git add .
git commit -m "Your commit message"
git checkout -b temp-branch
git checkout main
git rebase temp-branch
```