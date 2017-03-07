<?
namespace Core\Helpers;
class DBRes {
    private $result;

    const ASSOC_ID = 1;
    const ASSOC_KEY = 2;
    const ASSOC_KEY_FORCE = 3;

    public function __construct($result, $arows = 0, $srows = 0, $ins_id = false, $error = false, $orig_sql = false)
    {
        $this->result = $result;
        $this->a_rows = $arows;
        $this->s_rows = $srows;
        $this->ins_id = $ins_id;
        $this->sql = $orig_sql;
        $this->error = $error;
    }

    public function seek($offset){
        if($this->result && intval($offset) <= $this->s_rows){
            return mysqli_data_seek($this->result, intval($offset));
        }
        return false;
    }

    public function fetch(){
        if($this->result != false){
            if($row = mysqli_fetch_assoc($this->result)){
                return $row;
            }else
                return false;
        }else
            return false;
    }
    
    public function Rows(){
        return $this->s_rows;
    }

    public function Rows_affected(){
        return $this->a_rows;
    }

    public function absorb($fetch_type = 0, $assoc_key = ''){
        if($this->result != false){
            if($fetch_type == self::ASSOC_KEY && $assoc_key == '')
                $fetch_type = 0;

            switch($fetch_type){
                case 1:
                    $r = [];
                    while($row = mysqli_fetch_assoc($this->result))
                        $r[$row['id']] = $row;
                    return count($r) ? $r : false;
                    break;
                case 2:
                    $r = [];
                    while($row = mysqli_fetch_assoc($this->result))
                        $r[$row[$assoc_key]][] = $row;
                    return count($r) ? $r : false;
                    break;
                case 3:
                    $r = [];
                    while($row = mysqli_fetch_assoc($this->result))
                        $r[$row[$assoc_key]] = $row;
                    return count($r) ? $r : false;
                    break;
                default:
                    $r = [];
                    while($row = mysqli_fetch_assoc($this->result))
                        $r[] = $row;
                    return count($r) ? $r : false;
                    break;
            }
        }else
            return false;
    }
}
?>
