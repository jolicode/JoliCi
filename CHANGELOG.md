## Change Log

### v0.2.3 (2014/08/01 14:40 +00:00)
- [1690410](https://github.com/jolicode/JoliCi/commit/16904105468a718a705376baf9d9ac66bd280c64) Add php 5.6 support (@joelwurtz)
- [99a9dea](https://github.com/jolicode/JoliCi/commit/99a9dea3d196845545c21e1da0449865a900d4f1) Remove hack for phpenv, as new images use phpenv (@joelwurtz)
- [093a309](https://github.com/jolicode/JoliCi/commit/093a309cb1d804748cde099df618c21967809c48) Add jolici binary to composer. (@nubs)
- [d494e90](https://github.com/jolicode/JoliCi/commit/d494e90324f0d87cd6a705b41f825474763a094a) Remove non-available builds from filesystem. (@nubs)
- [0a715f8](https://github.com/jolicode/JoliCi/commit/0a715f86cb52fe815f379ff28c4bc03a9b746cf9) Add support for gush (@cordoval)
- [9714ab0](https://github.com/jolicode/JoliCi/commit/9714ab02dc5da5b1af78b4bb7ec4881de7c4620a) Use stable version for dependencies (@nubs)

### v0.2.2 (2014/06/26 22:20 +00:00)
- [239a2de](https://github.com/jolicode/JoliCi/commit/239a2de6cf5d0db31404dbb39ab6f4b9210aa921) Update dependencies versions (@joelwurtz)

### v0.2.1 (2014/05/15 15:36 +00:00)
- [ad1ba9a](https://github.com/jolicode/JoliCi/commit/ad1ba9a3eca7fb4a0e4f3f3e5aba59904d525127) Add ruby versions (@joelwurtz)
- [d697ee8](https://github.com/jolicode/JoliCi/commit/d697ee8eedcd0b33b42c2b5e89c918fb222eefbc) Encapsulate command in bash logged with profile support (@joelwurtz)
- [c9b86ce](https://github.com/jolicode/JoliCi/commit/c9b86ce4c63ceb430a10930e5a7345e70706a321) Add node js support (@joelwurtz)
- [9b1c684](https://github.com/jolicode/JoliCi/commit/9b1c684ebe11991253d76825847d79c91ad92ac8) Add support for env variables (@joelwurtz)
- [d1cf3b7](https://github.com/jolicode/JoliCi/commit/d1cf3b7966b385759acc351c299b067b653ac06d) Add timeout support, and set to 5 min by default (@joelwurtz)
- [9a551c8](https://github.com/jolicode/JoliCi/commit/9a551c8938f61ef74beaff3737b8dfa7c2091b9e) Add static progress bar when getting image from docker (@joelwurtz)
- [f6662c2](https://github.com/jolicode/JoliCi/commit/f6662c25c8ad55ead5b654a75a29668ffa35f236) Make php dockerfile work when using phpenv specific action (like symfony) (@joelwurtz)

### v0.2.0 (2014/02/21 15:16 +00:00)
- [7d85d78](https://github.com/jolicode/JoliCi/commit/7d85d78774eaf6a5d41c1cd98a4be4ce7497c054) Adding ruby to travis strategy (@joelwurtz)
- [71364c3](https://github.com/jolicode/JoliCi/commit/71364c3814e071cf63983f98933e11ec75fb5ea9) Add a default timeout to 10min for long running build (@joelwurtz)
- [7100908](https://github.com/jolicode/JoliCi/commit/7100908c6f78966bd750310b2abfa0af5173f1fb) Refactoring creation of Dockerfile with twig generation (@joelwurtz)

### v0.1.1 (2014/01/18 21:43 +00:00)
- [69276ca](https://github.com/jolicode/JoliCi/commit/69276ca8b982b1343519f4119d188a299258c40b) Add travis ci support (@joelwurtz)
- [8d469ec](https://github.com/jolicode/JoliCi/commit/8d469ecad0b696f72032e21f0d654ee8ad304c47) Add error management (@joelwurtz)
- [945a31a](https://github.com/jolicode/JoliCi/commit/945a31a1750c366eae15f1baeebf1aaff73001ff) Allow to override command when running test (@joelwurtz)