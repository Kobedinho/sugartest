# End-to-End Testing with Seedbed

Seedbed is our in-house testing framework for SugarCRM Mobile and SugarCRM Desktop apps. Seedbed is installed as an NPM module and is required to run our end-to-end tests.

## Getting Started

You can run Seedbed locally on your workstation or in Docker without installing it locally if that's your preference. To run Seedbed exactly as CI runs it, you'll have to run it in Docker.

# Running Seedbed in Docker

## Introduction to Docker

You'll need a basic grasp of Docker and containers in general if you plan to run Seedbed in Docker. Read [this Confluence page](https://sugarcrm.atlassian.net/wiki/display/EA/Introduction+to+Docker) for a brief introduction to get you started.

Please remember that if you plan to run Seedbed in Docker against a locally installed instance of Sugar on a Mac (running in MAMP, for example) you may not be able to specify http://localhost because localhost inside the container might actually point to a virtual machine running in the Mac OS X hypervisor. You should specify your Mac's IP address in the URL instead, and you can always test against deployed Honeycomb instances.

## Install Docker

You'll need to install Docker if you haven't already, so download and run the appropriate Docker installer for your platform:

[Get Docker for Linux](https://docs.docker.com/engine/installation/)  
[Get Docker for Mac](https://download.docker.com/mac/stable/Docker.dmg)  
[Get Docker for Windows](https://download.docker.com/win/stable/InstallDocker.msi)

## Running Tests in Docker

Start in your fork of Mango, inside the sugarcrm directory. You don't need to perform a yarn install because the docker image will do this for you automatically before running any tests. You *do* however need to mount your sugarcrm directory inside the docker container as /sugarcrm. To start a docker container with your sugarcrm directory mounted inside and run *all* of your Seedbed tests:
```
docker run --rm -p 5900:5900 -v ${PWD}:/sugarcrm registry.sugarcrm.net/seedbed/seedbed:latest -u http://your.sugar/instance
```
This starts a Docker container and runs Seedbed. By passing the -u argument only, you are running all of the end-to-end tests that reside in your fork of Mango on the specified sugar instance. Note that you can pass any other command-line arguments that are supported by Seedbed, such as -t "@create,@delete" to run all tests with the "create" or "delete" tags. Results and failures are stored in sugarcrm/tests/end-to-end/results and sugarcrm/tests/end-to-end/results_failures respectively.

You can watch the tests run with a VNC client. Mac OS X comes with a built-in VNC client that you can access by bringing Finder into focus and pressing Apple+K, or by clicking the "Go" menu and selecting "Connect to Server". Just provide an empty password to the VNC server and watch the tests run. Your server address is:
```
vnc://localhost:5900
```
Remember that you won't be able to connect to the VNC server until the tests begin running, after the yarn install finishes.

## Executing Arbitrary Commands Inside The Docker Container

To run the Docker container and drop into an interactive root shell with your local fork of Mango mounted and ready for any sort of interactive work you might want to do:
```
docker run -it --env DEV=true --rm -p 5900:5900 -v ${PWD}:/sugarcrm registry.sugarcrm.net/seedbed/seedbed:latest
```
As a matter of fact you can perform a yarn install and use nodejs without ever installing them on your Mac. Simply run the container in developer mode, as shown above, then fire away! All changes persist in your local Mango sugarcrm directory.

# Running Tests Without Docker

To run all Seedbed tests locally outside of Docker you'll need to install some external dependencies. [Click here](https://github.com/sugarcrm/Seedbed) to view those instructions. Once you've got all the external dependencies installed you'll need to do a yarn install inside sugarcrm/:
```
yarn install
```
The yarn install actually installs Seedbed and all of its nodejs dependencies. Finally you can execute the following command inside of sugarcrm/tests/end-to-end to run your tests:
```
node ci.js -u http://your.sugar/instance
```

Of course you can pass more CLI [arguments](https://github.com/sugarcrm/Seedbed/wiki/CLI) to run specific tests or tests with particular tags, just as you can in Docker.

# Understanding End-to-End Testing

## Maintain A Holistic View

Seedbed is built on Cucumber and, as a consequence, all end-to-end tests are written in English (Gherkin). Cucumber was designed as a collaborative tool to verify **holistic** software behavior, not specific individual bits of functionality. Put a different way, you want to validate the behavior of a feature just **as it would actually be used** by customers. This is very different from testing isolated pieces of logic or particular GUI functionality that may just be *part* of an overarching feature.

## Don't Test Every Edge Case

We don't want to use end-to-end tests to cover every single edge case of every feature in the application. The true purpose of these tests is to answer the following question: "What if a customer used this feature for its **primary purpose as it was intended to be used** right now? Does this feature provide the functionality that describes it?" Answering the previous question as "no" means we're not done with the feature yet. Always keep this in mind when writing end-to-end tests. More on that in a minute...

## End-to-End Tests Are Expensive

These tests are expensive (with regard to time) to write and maintain, requiring high-level knowledge about how the software should behave to satisfy customers requirements. This, coupled with how difficult it is to identify what a problem actually is when these tests fail, is why we want to focus most of our edge case testing on the lower levels of the testing pyramid, particularly at the unit and integration layers where tests are much less expensive to run and easier to maintain.

## Calling [The Three Amigos](http://www.velocitypartners.net/blog/2014/02/11/the-3-amigos-in-agile-teams/)

Since end-to-end tests use English phrases to describe how Sugar should behave when customers are using it, these tests really need to be written with **all three amigos present** during the planning phase. Writing tests this way is absolutely critical as we shall soon see...

## Feature Requirements ARE The Tests

Once product management has selected which features we're going to build right now, they need to sit down with developers and quality assurance to **have a conversation** about the feature that's about to be developed. From that conversation should come clear, accurate, and concise **English descriptions of how Sugar should behave** when customers are using the feature.

The English descriptions of how a feature is supposed to behave will serve as the **actual feature descriptions (Gherkin)** that make up our end-to-end tests. These feature descriptions will literally turn green or red as we run end-to-end tests depending on whether the feature is working as we described it. This concept is so critical that it deserves to be restated in a different way. The English statements that the three amigos settled on will turn green if the software is behaving as it was described, or red if the software is not behaving as it was described.

## Knowing Where You're Going

With clear English statements describing how the feature should behave, the scrum team can begin sprint planning. Development should **not** begin until these English statements accurately and clearly describe how the application should behave when this feature is being used. This means product management should not stop having a conversation with the other two amigos until they are satisfied that the English phrases produced by these meetings adequately describe the desired behavior of the feature that is about to be developed.

Not starting development until you have a clear goal makes a lot of sense when you think about it. After all, how can you arrive at your desired destination if you don't know exactly where you're going? Whether this is your ultimate end-of-the-road destination is irrelevant, especially in an agile organization. How you get to your **next** destination can be an agile process and that next destination ought to be **crystal clear** before a single line of code is written. 

## Cucumber is a Collaboration Tool

It's worth noting that even the author of Cucumber -- the functional testing framework upon which Seedbed is based -- designed it as [a collaborative tool](https://cucumber.io/blog/2014/03/03/the-worlds-most-misunderstood-collaboration-tool) first and foremost. Gherkin (English) is preferred as the best language for writing tests precisely because it allows developers, quality assurance, product managers, business analysts, and everyone else to **speak the same language** when discussing product features and behavior. When we describe every feature and its behavior in English, we can literally turn those descriptions into tests that turn green when they pass or red when they fail. This makes determining whether a feature is working as we intended crystal clear, and it keeps our end-to-end tests focused on the bigger picture where it belongs rather than deep in the weeds of implementation. Unit tests **are** the weeds, and may they grow without hindrance until they're no longer needed!

# Further Reading

Please refer to the [Seedbed Handbook](https://github.com/sugarcrm/Seedbed/wiki/Handbook) for detailed information about writing scenarios as well as step definitions to actually implement Seedbed tests.
