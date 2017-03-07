<?
namespace Core\Prototypes;

use Core\Core;

abstract class Model {
    protected $table;
    protected $fields;
    protected $primary = 'id';

    /**
     * @param $id
     * @return array|bool|null
     */
    public function find($id){
        $sql = sprintf('SELECT %s FROM %s WHERE %s = %s', implode(', ', $this->fields), $this->table, $this->primary, $id);
        return Core::$db->exec($sql)->fetch();
    }

    /**
     * @param array $filter
     * @param array $sort
     * @param $limit
     * @param $cnt
     * @return \Core\Helpers\DBRes
     * @throws \Exception
     */
    public function getList($filter = [], $sort = [], $limit = 0, $cnt = 0){
        $logic_list = [];
        foreach($filter as $key => $value)
            if(preg_match('/^(~|!|>|<)/i', $key, $ma)){
                unset($filter[$key]);
                $key = preg_replace('/^(~|!|>|<)/i', '', $key);
                $logic_list[$key] = $ma[1];
                $filter[$key] = $value;
            }

        $keys = array_intersect(array_keys($filter), $this->fields);
        $o_keys = array_intersect(array_keys($sort), $this->fields);
        $cnt = intval($cnt);
        $limit = intval($limit);
        $where = [];
        $order = [];

        if(count($keys)) foreach ($keys as $key){
            $logic = '=';
            if(is_array($filter[$key])){
                $logic =  (isset($logic_list[$key]) && $logic_list[$key] == '!') ? 'NOT IN' : 'IN';
                $where[] = sprintf("%s %s ('%s')", $key, $logic, implode("', '", $filter[$key]));
            }else{
                if(isset($logic_list[$key]) && $logic_list[$key] == '~')
                    $logic = 'LIKE';
                elseif(isset($logic_list[$key]))
                    $logic = $logic_list[$key];

                $where[] = sprintf("%s %s '%s'", $key, $logic, $filter[$key]);
            }
        }

        if($o_keys) foreach ($o_keys as $key)
            if(in_array(strtolower($sort[$key]), ['ask', 'desc']))
                $order[] = sprintf("%s %s", $key, $sort[$key]);

        $sql = sprintf('
            SELECT
                %s
            FROM %s
            %s
            %s
            %s',
            implode(', ', $this->fields),
            $this->table,
            count($where) ? "WHERE\n ".implode("\nAND ", $where) : '',
            count($order) ? "ORDER BY\n ".implode(",\n", $order) : '',
            !$cnt ? ($limit > 0 ? "LIMIT {$limit}" : '') : "LIMIT {$limit}, {$cnt}"
        );
        return Core::$db->exec($sql);
    }

    /**
     * @param $id
     * @return bool
     */
    public function drop($id){
        $sql = sprintf('DELETE FROM %s WHERE %s = %s', $this->table, $this->primary, $id);
        return Core::$db->exec($sql)->a_rows ? true : false;
    }

    /**
     * @param $id
     * @param $fields
     * @return bool
     * @throws \Exception
     */
    public function update($id, $fields){
        if(!$fields || !$id)
            return false;

        if(array_key_exists($this->primary, $fields))
            unset($fields[$this->primary]);

        $keys = array_intersect(array_keys($fields), $this->fields);

        if(count($keys)){
            $data = [];
            foreach($keys as $key)
                $data[] = sprintf("%s = '%s'", $key, $fields[$key]);

            $sql = sprintf('UPDATE %s SET %s WHERE %s = %s', $this->table, implode(', ', $data), $this->primary, $id);
            return Core::$db->exec($sql)->a_rows ? true : false;
        }else
            return false;
    }

    /**
     * @param $fields
     * @return bool
     */
    public function add($fields){
        if(!$fields)
            return false;

        if(array_key_exists($this->primary, $fields))
            unset($fields[$this->primary]);

        $keys = array_intersect(array_keys($fields), $this->fields);
        if(count($keys)){
            $data = [];
            foreach($keys as $key)
                $data[] = sprintf('"%s"', $fields[$key]);

            $sql = sprintf('INSERT INTO %s (%s) VALUES (%s)', $this->table, implode(', ', $keys), implode(', ', $data));
            return ($r = Core::$db->exec($sql)) && $r->a_rows ? $r->ins_id : false;
        }else
            return false;
    }
}