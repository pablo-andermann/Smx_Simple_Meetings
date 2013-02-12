<?php
namespace Smx\SimpleMeetings\Tests\WebEx;

require_once __DIR__.'/../../SmxSimpleMeetings.php';

use Smx\SimpleMeetings\Factory;

class MeetingTest extends \PHPUnit_Framework_TestCase
{
    
    private $WebExUsername;
    private $WebExPassword;
    private $WebExSitename;
    
    protected function setUp()
    {
        if(is_null($this->WebExUsername)){
            include __DIR__.'/../../config.local.php';
            $this->WebExUsername = $WebExUsername;
            $this->WebExPassword = $WebExPassword;
            $this->WebExSitename = $WebExSitename;
        }
    }
    
    public function testLoadXml()
    {
        $username = 'testuser';
        $password = 'testpass';
        $sitename = 'testsite';
        
        $meeting = Factory::SmxSimpleMeeting('WebEx','Meeting', $username, $password, $sitename);
        
        $xml = $meeting->loadXml('CreateMeeting');
        $this->assertInstanceOf('SimpleXmlElement', $xml);
    }
    
    public function testCreateMeetingWithDefaults()
    {
        
        $meeting = Factory::SmxSimpleMeeting('WebEx', 'Meeting', 
                $this->WebExUsername, $this->WebExPassword, $this->WebExSitename);
        $meeting->createMeeting(array('meetingPassword'=>'Sumi123'));
        $this->assertRegExp('/[0-9]{1,}/', $meeting->meetingKey);
        
        return $meeting;
    }
    
    /*
     * @depends testCreateMeetingWithDefaults
     */
    public function testGetHostJoinUrls()
    {
        $meeting = Factory::SmxSimpleMeeting('WebEx', 'Meeting', 
                $this->WebExUsername, $this->WebExPassword, $this->WebExSitename);
        $meeting->createMeeting(array('meetingPassword'=>'Sumi123'));
        
        $hostUrl = $meeting->startMeeting(true);
        $this->assertStringStartsWith('http', $hostUrl);
        
        $genericJoinUrl = $meeting->joinMeeting(true);
        $this->assertStringStartsWith('http', $genericJoinUrl);
        
        $specificJoinUrl = $meeting->joinMeeting(true,'Phillip',
                'phillips@corp.sumilux.com','Sumi123');
        $this->assertStringStartsWith('http', $specificJoinUrl);
    }
    
    public function testEditMeeting()
    {
        $meeting = Factory::SmxSimpleMeeting('WebEx', 'Meeting', 
                $this->WebExUsername, $this->WebExPassword, $this->WebExSitename);
        $meeting->createMeeting(array('meetingPassword'=>'Sumi123'));
        
        $options = array(
            'meetingName' => 'New Meeting Name',
            'duration' => '15'
        );
        
        $this->assertNotEquals($options['meetingName'], $meeting->meetingName);
        
        $meeting->editMeeting($options);
        
        $srvMeeting = $meeting->getServerMeetingDetails();
        
        $this->assertEquals($options['meetingName'], $srvMeeting->metaData->confName->__toString());
    }
    
    public function testDeleteMeeting()
    {
        $meeting = Factory::SmxSimpleMeeting('WebEx', 'Meeting', 
                $this->WebExUsername, $this->WebExPassword, $this->WebExSitename);
        $meeting->createMeeting(array('meetingPassword'=>'Sumi123'));
        
        $this->assertInstanceOf('\\Smx\\SimpleMeetings\\Base\\Meeting', $meeting->deleteMeeting());
    }
    
    public function testGetMeetingList()
    {
        $meeting = Factory::SmxSimpleMeeting('WebEx', 'Meeting', 
                $this->WebExUsername, $this->WebExPassword, $this->WebExSitename);
        $list = $meeting->getMeetingList();
        $this->assertInstanceOf('\\Smx\\SimpleMeetings\\Base\\ItemList', $list);
    }
}