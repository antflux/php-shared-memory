<?php
    class memory_cache{
        public function __construct()
        {

        }

        public function set($data, $name, $timeout=0) {
            // delete cache
            $cacheName=self::get_cache_id($name);
            $id=shmop_open($cacheName, "a", 0, 0);
            shmop_delete($id);
            shmop_close($id);

            // get id for name of cache
            $id=shmop_open($cacheName, "c", 0644, strlen(serialize($data)));

            // return int for data size or boolean false for fail
            if ($id) {
                $this->set_timeout($name, $timeout);
                return shmop_write($id, serialize($data), 0);
            }
            else return false;
        }

       public  function get($name) {
            if (!$this->check_timeout($name)) {
                $cacheName=self::get_cache_id($name);
                $id=shmop_open($cacheName, "a", 0, 0);

                if ($id) $data=unserialize(shmop_read($id, 0, shmop_size($id)));
                else return false;          // failed to load data

                if ($data) {                // array retrieved
                    shmop_close($id);
                    return $data;
                }
                else return false;          // failed to load data
            }
            else return false;              // data was expired
        }

        private static function  get_cache_id($name) {
            return hexdec($name);
        }
        function set_timeout($name, $int) {
            $timeout=$int==0?new DateTime('2030-03-29 08:00:00'):new DateTime(date('Y-m-d H:i:s'));
            date_add($timeout, date_interval_create_from_date_string("$int seconds"));
            $timeout=date_format($timeout, 'YmdHis');

            $id=shmop_open(100, "a", 0, 0);
            if ($id) $tl=unserialize(shmop_read($id, 0, shmop_size($id)));
            else $tl=array();
            shmop_delete($id);
            shmop_close($id);

            $tl[$name]=$timeout;
            $id=shmop_open(100, "c", 0644, strlen(serialize($tl)));
            shmop_write($id, serialize($tl), 0);
        }

        function check_timeout($name) {
            $now=new DateTime(date('Y-m-d H:i:s'));
            $now=date_format($now, 'YmdHis');

            $id=shmop_open(100, "a", 0, 0);
            if ($id) $tl=unserialize(shmop_read($id, 0, shmop_size($id)));
            else return true;
            shmop_close($id);

            $timeout=$tl[$name];
            return (intval($now)>intval($timeout));
        }
    }

    $data=array("user"=>array("user_name"=>'mike',"user_pass"=>'test'),array("user_name"=>'yahu',"user_pass"=>'yahuu'));
    //save_cache($data,"db_user");
    $cache=new memory_cache();
    var_dump($cache->get('db_user')) ;