<?php

PHPUnit_Util_Filter::addFileToFilter(__FILE__);

if (!is_dir('./fixtures/001_VersionControl_Git'))
{
  if (!is_file('./fixtures.tar.gz'))
  {
    throw new Exception('You don\'t have fixtures.tar.gz. You need it to execute the unit test. Please download and expand it. (See README file)'.PHP_EOL);
  }
  else
  {
    throw new Exception('You need to expand fixtures.tar.gz to execute the unit test.'.PHP_EOL);
  }
}

