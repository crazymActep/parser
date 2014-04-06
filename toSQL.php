<?php
/*
echo "<pre>";
print_r(json_decode(file_get_contents('php://input')));
echo "</pre>";
*/
        // функция подключения к БД
        function db_connection($query) {
                mysql_connect('localhost','root','')
    			OR die(fail('Could not connect to database.'));
                mysql_select_db('parser');
                mysql_query("set names utf8");  // установка кодировки соединения
                return mysql_query($query);
       }

//$table_name = $massiv[0];
// принмаем данные - многомерный массив
$data = json_decode(file_get_contents('php://input'));

// вырезаем информационный массив (с именем таблицы и столбцов)
$info = array_pop($data);

$tableName = $info[0];  // имя таблицы для запроса SQL на создание таблицы
// фильтрация
$tableName = strip_tags($tableName);
$tableName = trim($tableName);
$tableName = htmlspecialchars($tableName);
$tableName = mysql_real_escape_string($tableName);

$col_1 = $info[1];
$col_2 = $info[2];
$col_3 = $info[3];
$col_4 = $info[4];
$col_5 = $info[5];

/**/
// запрос SQL - создание таблицы
$query = "CREATE TABLE $tableName (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `productName` varchar(255) DEFAULT NULL,
  `project` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `unitPrice` int(11) DEFAULT NULL,
  `totalPrice` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8";
$result = db_connection($query);

// перебор массива и запрос SQL - отправка значений в БД
foreach($data as $row => $field){
    // приведене к типу
    $field[1] = (integer)$field[1];
    $field[2] = (integer)$field[2];
    $field[3] = (integer)$field[3];
    $field[4] = (integer)$field[4];
    $query = "INSERT INTO $tableName SET productName='$field[0]', quantity='$field[1]', unitPrice='$field[2]', totalPrice='$field[3]', project='$field[4]'";
    $result = db_connection($query);
}

        if ($result) {
            success('таблица успешно создана в БД');
        } else {
            fail('ошибка добавления в БД');
        }
        
        
        
        	// ф-ии ошибки или успеха
        	function fail($message) {
        		die(json_encode(array('status' => 'fail', 'message' => $message)));
        	}
        	function success($message) {
        		die(json_encode(array('status' => 'success', 'message' => $message)));
        	}
/**/


/**/
/*
echo "<pre>";
print_r($massiv);
echo "</pre>";
*/

/*
echo "<pre>";
print_r($info);
echo "</pre>";
	
}
    foreach ($massiv[1] as $row => $field) {
        echo $field."</br>";
        //echo $field[1];
        $query = "INSERT INTO new_table SET first='$field', second= '$field', three='$field', four'$field', five='$field'";
        $result = db_connection($query);


    }
*/ 

/*
//extract($podMassive, EXTR_PREFIX_ALL, "pp");
$podMassive = array("z"=>"zero","o"=>"one");
extract($podMassive, EXTR_PREFIX_ALL, "pp");
echo $pp2;

        echo $pp_0."</br>";
        echo $pp_1."</br>"; 
        echo $pp_2."</br>"; 
        echo $pp_3."</br>"; 
        echo $pp_4."</br>";
*/

?>