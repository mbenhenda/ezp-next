TODO
====

These are open todo's that has been voted on and decided.
Smaller todo's can be found inline as code comments, search for @todo/@TODO/TODO.


Proxy
-----
Switch to proxy pattern with implementation for Location, Content, Section, Type/Group and User.
Needs a sprint story and estimates, but depends on decision on "API VS $dynamicProperties" discussion.


Struct inheritance
------------------
Reduce code / doc duplication on struct objects in Persistence by creating 'Struct' class on classes that already have
Create/Update struct classes. This struct class can then contain the proerties that are in common between Create/Update
struct classes + the value object they bnellong to.
Need possibly a small story or just time to do it.

