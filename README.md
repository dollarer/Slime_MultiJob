Slime MultiJob
========

Purpose:

1、Use for async deal job to make main processes none-blocking

2、Using multi-process mode to deal job faster

3、Low aggregation, easily integration in any php-framework or your own project


Why not multi process pool:

I have wrote a multi process pool by using php before, communicate by pipe, and it works.
However, after the worker process finish a job, it's hard to do a full memory release like fpm by using php.
So if the worker script has memory leak, the result is unfortunately.


Next:

It will be a MetalSlime MultiJob write by c, using multi process pool.