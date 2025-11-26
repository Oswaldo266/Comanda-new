<?php
// models/observers/NotificationManager.php
class NotificationManager {
    private $observers = [];
    
    public function attach($observer) {
        $this->observers[] = $observer;
    }
    
    public function detach($observer) {
        $key = array_search($observer, $this->observers, true);
        if ($key !== false) {
            unset($this->observers[$key]);
        }
    }
    
    public function notify($event, $data) {
        foreach ($this->observers as $observer) {
            $observer->update($event, $data);
        }
    }
}