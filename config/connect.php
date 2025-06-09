<?php
// Include db.php to get the constants (host, user, pass, db)
include_once('db.php');  

class database {
    public $host = db_host;  // This uses 'mysql' (from db.php)
    public $user = db_user;  // Uses 'smsuser' (from db.php)
    public $pass = db_pass;  // Uses 'smspassword' (from db.php)
    public $db = db_name;    // Uses 'student_management' (from db.php)
    public $result;
    public $conn;

    // Constructor to initialize connection
    public function __construct(){
        $this->connection(); // Connect to the DB
        $this->set_institute_info();
        date_default_timezone_set('Asia/Dhaka');
    }

    // Database connection function
    public function connection(){
        // Connect to the MySQL database using the credentials from db.php
        $this->conn = mysqli_connect($this->host, $this->user, $this->pass, $this->db);

        // If connection fails, die with a detailed error message
        if(!$this->conn){
            die("Connection failed: " . mysqli_connect_error());
        }
        return 1;  // Connection successful
    }

    // Utility functions
    public function date(){
        return $this->get_now_time();
    }

    public function get_now_time(){
        return date("Y-m-d H:i:s", time());
    }

    // Other utility functions for the app...
    public function set_login_user($uid, $ip, $browser){
        $this->login_user = $uid;
        $this->ip = $ip;
        $this->browser = $browser;
    }

    public function select($query){
        return $this->result = mysqli_query($this->conn, $query);
    }

    public function set_institute_info(){
        $sql = "SELECT * FROM setting";
        $info = $this->get_sql_array($sql);
        $img = "upload/custom_content/";
        // Check if $info has data to avoid undefined offset errors
        $this->site_name = isset($info[0]['option_value']) ? $info[0]['option_value'] : 'Default Institute';
        $this->sort_name = isset($info[1]['option_value']) ? $info[1]['option_value'] : 'Default';
        $this->address = isset($info[2]['option_value']) ? $info[2]['option_value'] : '';
        $this->phone = isset($info[5]['option_value']) ? $info[5]['option_value'] : '';
        $this->email = isset($info[6]['option_value']) ? $info[6]['option_value'] : '';
        $this->logo = isset($info[4]['option_value']) ? $img . $info[4]['option_value'] : '';
        $this->main_logo = isset($info[3]['option_value']) ? $img . $info[3]['option_value'] : '';
        $this->msg = isset($info[1]['option_value']) ? "@" . $info[1]['option_value'] : '@Default';
    }

    public function date_to_string($date){
        return date("d M Y h:i:A", strtotime($date));
    }

    public function get_select_last_id($query){
        if(mysqli_query($this->conn, $query)){
            return mysqli_insert_id($this->conn);  // Return the last inserted ID
        } else {
            return 0;
        }
    }

    public function get_sql_array($sql){
        $info = array();
        $res = $this->select($sql);
        if ($res) {
            while($row = mysqli_fetch_array($res)) {
                $sub = $this->process_mysql_array($row);
                array_push($info, $sub);
            }
            mysqli_free_result($res);
        }
        return $info;
    }

    // Added method to process MySQL row
    public function process_mysql_array($row) {
        return (array) $row; // Convert to associative array
    }

    // Handling SQL insert, update, delete operations
    public function action_link($table){
        $index["batch"] = "batch_list";
        $index["student"] = "student_list";
        $index["program"] = "program_list";
        $index["subject"] = "subject_list";
        $index['exam'] = "exam_list";
        $index['theme'] = "theme";
        return (isset($index[$table])) ? $index[$table] : "---";
    }

    public function insert_sql($arr, $table){
        $sql = "";
        $sql .= "INSERT INTO " . $table;
        $sql .= " (" . implode(",", array_keys($arr)) . ") VALUES ";
        $sql .= " ('" . implode("','", array_values($arr)) . "')";
        return $sql;
    }

    public function update_sql($arr, $table){
        $sql = "";
        $sql .= "UPDATE " . $table . " SET ";
        $condition = "";
        $size = sizeof($arr);
        $c = 0;
        foreach ($arr as $key => $value) {
            $condition .= $key . "='" . mysqli_real_escape_string($this->conn, $value) . "'";
            if($c != $size - 1) $condition .= ",";
            $c++;
        }
        $sql .= $condition;
        $sql .= " WHERE id=" . (int)$arr['id'];
        return $sql;
    }

    public function get_previous_data($table, $id){
        $sql = "SELECT * FROM $table WHERE id=" . (int)$id;
        $info = $this->get_sql_array($sql);
        if(isset($info[0])) return json_encode($info[0]);
    }

    public function sql_action($table, $action, $info, $msg = "yes"){
        $flag = 0;
        $action_name = "";
        
        if($action == "update"){
            $action_name = "Update " . $table;
            $sql = $this->update_sql($info, $table);
        } else if($action == "insert"){
            $action_name = "Insert New " . $table;
            $sql = $this->insert_sql($info, $table);
        } else if($action == "delete"){
            $id = (int)$info['id'];
            $action_name = "Delete " . $table;
            $sql = "DELETE FROM $table WHERE id=$id";
        }

        $present_data = "";
        $previous_data = "";

        if($table != "login"){
            if($action != "insert"){
                if($table != "site_activity"){
                    $previous_data = $this->get_previous_data($table, $info['id']);
                }
            }
            if($action == "insert"){
                $res = $this->get_select_last_id($sql);
                if($table != "site_activity") $present_data = $this->get_previous_data($table, $res);
            } else {
                $res = $this->select($sql);
                if($table != "site_activity") $present_data = $this->get_previous_data($table, $info['id']);
            }
        } else {
            $res = 1;
        }

        if($res) $flag = 1;

        if($flag == 1 && $table != "chat" && $table != "result" && $table != "student_attendence" && $table != "site_activity" && $this->login_user != ""){
            $activity = array();
            $table_id = ($action == "insert") ? $res : $info['id'];
            $login = ($table == "login") ? 1 : 0;
            $activity['user_id'] = $this->login_user;
            $activity['table_name'] = $table;
            $activity['action_type'] = $action;
            $activity['login'] = $login;
            $activity['table_id'] = $table_id;
            $activity['date'] = $this->date();
            $activity['ip'] = $this->ip;
            $activity['browser'] = $this->browser;
            $activity['present_data'] = $present_data;
            $activity['previous_data'] = $previous_data;
            $this->sql_action("site_activity", "insert", $activity, "no");
        }

        if($msg == "yes") $link = $this->action_link($table);

        if($flag == 1 && $msg == "yes") echo "<script>alert('Successfully $action_name!'); document.location='$link.php';</script>";
        else if($msg == "yes") echo "<script>alert('Failed...Please Again Try!'); document.location='$link.php';</script>";
        if($flag == 0) echo("Error description: " . mysqli_error($this->conn));
        if($msg == "no") return $flag;
    }
}
?>