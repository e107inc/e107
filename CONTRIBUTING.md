# e107 v2 
[![Join the chat at https://gitter.im/e107inc/e107](https://badges.gitter.im/e107inc/e107.svg)](https://gitter.im/e107inc/e107?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)

## How to contribute 

Contributing to e107 is quite easy if you keep in mind the following tips, to avoid some pifalls.

### The big picture

1. You need to install git on your local computer to be able to work with github! 
2. Create a fork of e107 by clicking on the "Fork" button on the top right corner of the project page.
3. Once the forking process has finished, click on the green "Clone or download" and copy the shown url.
4. Clone your fork to your local computer by running the `git clone [The previously copied url]` command.
5. Make sure, your copy "knows" from the original repo by running the following command `git remote add upstream https://github.com/e107inc/e107.git`
6. Create a new branch for each issue you tackle by running `git checkout [branch name e.g. fix_IssueNr]`
7. Work on your fix and add the changed file to your commit by running `git add [changed file]`
8. Commit your work by running `git commit -m [message]` e.g. for a message: "Fixes #5432 added new method to solve this" 
9. Push your commit to the remote repo (your online fork) by running `git push -u origin [branch name]`
10. Do NOT forget to switch the branch back to master by running `git checkout master`!
11. Now the fix is online at Github and you can now create the push request by clicking on "Compare & pull request" on YOUR forks project page. Enter some more details to the pull request to explain what you have done and click on "Create pull request"
12. Congratulation! You have created your first pull request!


### More in detail 

##### Make sure ... 

..., you have installed git on your local computer. You can gownload it [here](https://git-scm.com/downloads).
As i work on windows, i can only tell what to do on windows, but the git command itself are independent of the operating system.
      

##### Fork e107

I think the forking process is described enough in "The big picture". Please refer to the first 3 points there.


##### Clone your repo

Open a windows explorer and navigate to the folder where you want to place the  local copy of your repo. Usually, this is the webserver document root, but you can use whatever folder you like.
Right click on the folder and select `Git Bash here`. This will open a terminal window, and should show the same path as the folder you selected.
Enter the following command into this window `git clone [The previously copied url]` where "[The previously copied url]" must be replaced with the clone url of your repo (e.g. https://github.com/example/e107.git) and hit enter.
The cloning takes a while as the whole repo is downloaded to your local computer.
Maybe the best time for a cup of coffee, a tea or something else ...
Once this is done, you have cloned your first repo to your local computer!


##### Intermezzo

After you have cloned the repo, you should tell git, that your local copy has a "big brother" (the original e107 repo).
Run `git remote add upstream https://github.com/e107inc/e107.git`. This tells your repo, that there is another remote repo called "upstream" (the name can be different, but you should use "upstream" as i will use it again later).
This command has to be run only once!


##### Create branches for each issue you're working on

Now you are ready to fix whatever issue you find, add whatver feature the world is waiting for... nearly!
Before creating a new branch, make sure you are at the "master" branch. 
So first run `git checkout master` to make this sure!
Now create a new branch `git checkout [branch name e.g. fix_IssueNr]` where "IssueNr" stands for the issue number on GitHub. But it's up to you how you name your branches.
Depending on the editor you use (e.g. PhpStorm, Visual Studio Code, or whatever), they have usually git support included. Means, they git you command to create/switch branches or to add and commit files, etc. You should looks for it, but you will see, you will need the commandline for some tasks.


##### Adding & Commiting

Once you have your changes ready, make sure you are in the branch you need to be.
Run `git add [changed file]` on each file you changed or created.
Run `git commit -m [message]` to commit your changes to your local repository. Always include a short message to explain what has done. 
** Hint ** Using "Fixes #IssueNr" or "Closes #IssueNr" (where IssueNr is the issue nr in the GitHub issue list https://github.com/e107inc/e107/issues) directly closes the issue once the pull request is merged!

At this stage, you stages are still "only" locally on your computer. You will need to ...


##### Push, push, baby ...

Usually, when not working on a new branch, it would be enough to run `git push` and your changes would be uploaded to YOUR repo at GitHub.
But in case of a fresh branch, you need to be more specific what git should do.
Run `git push -u origin [branch name]`.
This tells git to push the new branch "branch name" (and it's contents) to the "origin" (the name of your remote repo) and to add it to the branches to "watch". That means, the next time you work on this branch and you want to push your work, you simply run the `git push` command, because now git is aware of this branch and knows where to push this.
Once this is finished, you should open the project page of you repo on GitHub and you will see, that there is a "yellow" message stating, that branch "branch name" has just been added and gives you a new button calling "Compare & pull request".


##### FULL PULL!

When you click on that green "Compare & pull request" button, a new page opens where you have the chance to enter more detailed information on your work. You should always include the issue nr (if you worked on an issue) or at least describe what you have done, to make it easier for the guy who has to merge your work to know what you wanted to achieve.
Just a quick "Fixed #2543" isn't enough. It should be "a little but more ...".
Well, once you clicked on "Create pull request", it's done. Now you have to wait if your pull request get's merged or maybe there is also some questions about it. But be aware, that this may take up to a few days...


##### And now? What if the official e107 repo changes?

Now, it comes quite handy, that we told our local copy that there is a "big brother".
Run `git pull --rebase upstream master` and of course `git checkout master`
This tells git to push the latest changes from the "upstream" (the official e107 repo) and to "rebase" the local copy to the same status.
This is the only possibility to make sure that not "old changes" in the official repo are pushed twice and start creating a mess ...