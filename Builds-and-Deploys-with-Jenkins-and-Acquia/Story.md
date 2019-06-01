# Story

During a 9pm code deployment, we had a deployment setup that did the builds and deploys happen sequentially and every time. As we were running the all-in-one build and deploy process it would get a good way through the theme build and suddenly die. After some digging we found that one of the JS dependencies had an issue that brought down their code repository (and completely hosed our build and deploy process).

That repository was down for about an hour. From that night on we decided to divorce our build process from our deployment process. That is how I came to this solution.