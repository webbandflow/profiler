# \WebbAndFlow\Profiler

Client library for (Webb & Flow Profiler MicroService)[https://profiler.services.webbandflow.co.uk/]

## Usage

Profiling takes up memory, so it must be enabled manually.

    Profiler::enable()
    
It can also be enabled automatically, if the request has a PROFILER or PROFILE query parameter

    Profiler::enableIfHasProfile()
    
Data tracking

    Profiler::addEvent($event, $param1 = null, $param2 = null)
    Profiler::startProcess($process, $param1 = null, $param2 = null)
    Profiler::finishProcess($process)
    
At the end of the script run, the collected data must be sent to the MicroService:

    Profiler::saveProfile($projectId, $profile, $entity)
    
Typical usage:

    <?php
    
    // autoloader or manual file include
    
    Profiler::enableIfHasProfile();
    
    // ...
    // custom code, profiler data tracking
    // ...
    
    // at the end of file:
    if (Profiler::isEnabled()) {
        Profiler::saveProfile(
            $_SERVER['HTTP_HOST'],
            Profiler::getProfile(),
            $_SERVER['REQUEST_URI']
        );
    }
    
