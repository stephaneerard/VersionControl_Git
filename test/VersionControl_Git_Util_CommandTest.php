<?php

chdir(dirname(__FILE__));
set_include_path(get_include_path().PATH_SEPARATOR.realpath('../../'));

require_once 'PHPUnit/Framework.php';
require_once 'VersionControl/Git.php';

class DummyGitCommand extends VersionControl_Git_Util_Command
{
  public function getCommandString($arguments = array(), $options = array())
  {
    return $this->createCommandString($arguments, $options);
  }

  public function getProperty($name)
  {
    return $this->$name;
  }
}

class VersionControl_Git_Util_CommandTest extends PHPUnit_Framework_TestCase
{
  public function testConstruct()
  {
    $git = new VersionControl_Git('./fixtures/001_VersionControl_Git');
    $instance = new DummyGitCommand($git);

    $this->assertTrue($instance instanceof VersionControl_Git_Util_Command);
  }

  public function testSetSubCommand()
  {
    $git = new VersionControl_Git('./fixtures/001_VersionControl_Git');
    $instance = new DummyGitCommand($git);

    $this->assertEquals($instance->getProperty('subCommand'), '');

    $instance->setSubCommand('subcommand');

    $this->assertEquals($instance->getProperty('subCommand'), 'subcommand');
  }

  public function testSetOptions()
  {
    $git = new VersionControl_Git('./fixtures/001_VersionControl_Git');
    $instance = new DummyGitCommand($git);

    $this->assertEquals($instance->getProperty('options'), array());

    $options1 = array('option1' => time() * 1, 'option2' => time() * 2);
    $instance->setOptions($options1);
    $this->assertEquals($instance->getProperty('options'), $options1);

    $options2 = array('option3' => time() * 3, 'option4' => time() * 4);
    $instance->setOptions($options2);
    $this->assertEquals($instance->getProperty('options'), $options2);
  }

  public function testSetArguments()
  {
    $git = new VersionControl_Git('./fixtures/001_VersionControl_Git');
    $instance = new DummyGitCommand($git);

    $this->assertEquals($instance->getProperty('arguments'), array());

    $arguments1 = array(time() * 1, time() * 2);
    $instance->setArguments($arguments1);
    $this->assertEquals($instance->getProperty('arguments'), $arguments1);

    $arguments2 = array(time() * 3, time() * 4);
    $instance->setArguments($arguments2);
    $this->assertEquals($instance->getProperty('arguments'), $arguments2);
  }

  public function testSetOption()
  {
    $git = new VersionControl_Git('./fixtures/001_VersionControl_Git');
    $instance = new DummyGitCommand($git);

    $options1 = array('option1' => time() * 1, 'option2' => time() * 2);
    $instance->setOptions($options1);
    $this->assertEquals($instance->getProperty('options'), $options1);

    $newOption = time() * 3;
    $instance->setOption('option1', $newOption);
    $this->assertEquals($instance->getProperty('options'), array('option1' => $newOption, 'option2' => $options1['option2']));
  }

  public function testAddArgument()
  {
    $git = new VersionControl_Git('./fixtures/001_VersionControl_Git');
    $instance = new DummyGitCommand($git);

    $arguments = array(1, 2, 3);
    $instance->setArguments($arguments);
    $this->assertEquals($instance->getProperty('arguments'), $arguments);

    $instance->addArgument(4);
    $this->assertEquals($instance->getProperty('arguments'), array(1, 2, 3, 4));
  }

  public function testAddDoubleDash()
  {
    $git = new VersionControl_Git('./fixtures/001_VersionControl_Git');
    $instance = new DummyGitCommand($git);

    $this->assertEquals($instance->getProperty('doubleDash'), false);
    $instance->addDoubleDash(true);
    $this->assertEquals($instance->getProperty('doubleDash'), true);
    $instance->addDoubleDash(false);
    $this->assertEquals($instance->getProperty('doubleDash'), false);
  }

  public function testCreateCommandStringException()
  {
    $this->setExpectedException('PEAR_Exception');

    $git = new VersionControl_Git('./fixtures/001_VersionControl_Git');
    $instance = new DummyGitCommand($git);

    $instance->getCommandString();
  }

  public function testCreateCommandString()
  {
    $git = new VersionControl_Git('./fixtures/001_VersionControl_Git');
    $i1 = new DummyGitCommand($git);

    $i1->setSubCommand('subcommand');

    $this->assertEquals($i1->getCommandString(), '/usr/bin/git subcommand');
    $this->assertEquals($i1->getCommandString(array('a1', 'a2')), '/usr/bin/git subcommand \'a1\' \'a2\'');
    $this->assertEquals($i1->getCommandString(array(), array('o1' => 'v1', 'o2' => true, 'o3' => false, 'o' => true)), '/usr/bin/git subcommand --o1=\'v1\' --o2 -o');
    $this->assertEquals($i1->getCommandString(array('a1', 'a2'), array('o1' => 'v1', 'o2' => true, 'o3' => false, 'o' => true)), '/usr/bin/git subcommand --o1=\'v1\' --o2 -o \'a1\' \'a2\'');

    $i2 = clone $i1;
    $i2->setArguments(array('A1', 'A2'));

    $this->assertEquals($i2->getCommandString(), '/usr/bin/git subcommand \'A1\' \'A2\'');
    $this->assertEquals($i2->getCommandString(array('a1', 'a2')), '/usr/bin/git subcommand \'A1\' \'A2\' \'a1\' \'a2\'');
    $this->assertEquals($i2->getCommandString(array(), array('o1' => 'v1', 'o2' => true, 'o3' => false, 'o' => true)), '/usr/bin/git subcommand --o1=\'v1\' --o2 -o \'A1\' \'A2\'');
    $this->assertEquals($i2->getCommandString(array('a1', 'a2'), array('o1' => 'v1', 'o2' => true, 'o3' => false, 'o' => true)), '/usr/bin/git subcommand --o1=\'v1\' --o2 -o \'A1\' \'A2\' \'a1\' \'a2\'');

    $i3 = clone $i1;
    $i3->setOptions(array('O1' => 'V1', 'o2' => 'V2'));

    $this->assertEquals($i3->getCommandString(), '/usr/bin/git subcommand --O1=\'V1\' --o2=\'V2\'');
    $this->assertEquals($i3->getCommandString(array('a1', 'a2')), '/usr/bin/git subcommand --O1=\'V1\' --o2=\'V2\' \'a1\' \'a2\'');
    $this->assertEquals($i3->getCommandString(array(), array('o1' => 'v1', 'o2' => true, 'o3' => false, 'o' => true)), '/usr/bin/git subcommand --O1=\'V1\' --o2 --o1=\'v1\' -o');
    $this->assertEquals($i3->getCommandString(array('a1', 'a2'), array('o1' => 'v1', 'o2' => true, 'o3' => false, 'o' => true)), '/usr/bin/git subcommand --O1=\'V1\' --o2 --o1=\'v1\' -o \'a1\' \'a2\'');

    $i4 = clone $i1;
    $i4->addDoubleDash(true);

    $this->assertEquals($i4->getCommandString(), '/usr/bin/git subcommand --');
    $this->assertEquals($i4->getCommandString(array('a1', 'a2')), '/usr/bin/git subcommand \'a1\' \'a2\' --');
    $this->assertEquals($i4->getCommandString(array(), array('o1' => 'v1', 'o2' => true, 'o3' => false, 'o' => true)), '/usr/bin/git subcommand --o1=\'v1\' --o2 -o --');
    $this->assertEquals($i4->getCommandString(array('a1', 'a2'), array('o1' => 'v1', 'o2' => true, 'o3' => false, 'o' => true)), '/usr/bin/git subcommand --o1=\'v1\' --o2 -o \'a1\' \'a2\' --');
  }

  public function testExecuteException()
  {
    $this->setExpectedException('PEAR_Exception');

    $git = new VersionControl_Git('./fixtures/001_VersionControl_Git');
    $i1 = new DummyGitCommand($git);

    $i1->setSubCommand('subcommand')
      ->execute();
  }

  public function testExecute()
  {
    $git = new VersionControl_Git('./fixtures/001_VersionControl_Git');
    $i1 = new DummyGitCommand($git);

    $result = $i1->setSubCommand('log')
      ->setOption('oneline')
      ->setOption('grep', 'initial')
      ->execute();

    $this->assertEquals(trim($result), 'b8adc72 initial commit');
  }
}
