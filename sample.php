<html>
<head>
    <meta http-equiv="content-type" content="text/html" charset="UTF-8" />
 	<style type="text/css">
        table{
            background-color: #C4C4C4;
        }
        input {
            border-radius: 10px;
            -moz-border-radius: 10px;
            -webkit-border-radius: 10px;
        }
        form{
            margin: auto;
        }

        td[contenteditable=true]:hover{
            background-color: #ffc;
        }
        td[contenteditable=true]:focus{
           background-color: #ffa;
           border: 1px dotted #000;
        }
        .markedRed{
            background-color: #FFB5B2;
        }
        #resultMsg{
            border-radius: 10px;
            -moz-border-radius: 10px;
            -webkit-border-radius: 10px;
            font-size: 20px;
            padding: 0px 5px;
        }
        .success{
            background-color: #C6FFD5;
            
        }
        .fail{
            background-color: #FFC5CF;
        }
        
     </style>
     <script src="jquery-2.0.2.js"></script>
     <script type="text/javascript">
        $(function(){
            
            
            $("body").attr("background-color","");
                
      ////////////////////////////////////////////////////////////////////////////
      // добвалене кнопок к строкам и столбцам
      ////////////////////////////////////////////////////////////////////////////             
            // вставка ряда новых строк и яйчеек с кнопками в начало таблицы
            var numOf_td = $('tr#firstRow td').size();  // определение числа яйчеек в первой строке таблицы
            $("<tr id='delRow'></tr>").insertBefore('#firstRow');   // вставка ряда
            $("<tr id='selectRow'></tr>").insertAfter('#delRow');   // вставка ряда            
            for (i=0; i<numOf_td; i++) {    //  вставка числа яйчеек взависимсоти от кол-ва их в стандартной строке таблицы
                // вставка строк с кнопками удаления столбцов и выпадающими списками            
	           $("<td><input type='button' class='col_DEL' value='X' /></td>").appendTo('#delRow');
               $("<td><select  class='sel' size='1'><option value='empty'>НЕ ВЛИВАТЬ</option><option class='selProductName' value='ProductName'>ProductName</option><option  id='selQuantity' value='Quantity'>Quantity</option><option id='selUnitPrice' value='UnitPrice'>UnitPrice</option><option id='selTotalPrice' value='TotalPrice'>TotalPrice</option><option sel='selProject' value='Project'>Project</option></select></td></td>").appendTo('#selectRow');               
            }
            // добавляет в каждую строку (кроме первых двух) яйчейки с кнопкой для удаления строк
            $('tr').slice(2).append("<td><input type='button' class='row_DEL' value='X' /></td>");
            
            
            // удаление ряда
            $('.row_DEL').on('click', function(){
                $(this).closest('tr').remove();
            });
            // удаление колонки
            $('.col_DEL').on('click', function(){
                var colIndex = $(this).index('.col_DEL');
                $('tr').each(function(){
                    $('td:eq(' + colIndex + ')', this).remove();
                });
            });
            
            
            
            // маркировка красным при наводке на кнопку удаления колонки
            $('.col_DEL').on('mouseover', function(){
                var colIndex = $(this).index('.col_DEL');
                $('tr').each(function(){
                    $('td:eq(' + colIndex + ')', this).addClass('markedRed');
                });
            });            
            // снятие маркировки красным
            $('.col_DEL').on('mouseout', function(){
                var colIndex = $(this).index('.col_DEL');
                $('tr').each(function(){
                    $('td:eq(' + colIndex + ')', this).removeClass('markedRed');
                });
            });
            
            // маркировка красным при наводке на кнопку удаления строки
            $('.row_DEL').on('mouseover', function(){
                $(this).closest('tr').addClass('markedRed');
            });
            // снятие маркировки красны
            $('.row_DEL').on('mouseout', function(){
                $(this).closest('tr').removeClass('markedRed');
            });                                      
                        
      ////////////////////////////////////////////////////////////////////////////////
      ////////////////////////////////////////////////////////////////////////////////

            // ф-ия фильтра точек и запятых в цене
            // приводит к типу 20555.32 (float для MYSQL) если изначально был точки, запятые или пробелы      
            function priceFilter(o){
                var price = $(o).text();
                price = price.toString(price);  // преобразование в строку
                price = price.replace(",","."); // заменяет запятую на точку (3 раза для удаленя несколькх есл есть)
                price = price.replace(",",".");  
                price = price.replace(",",".");
                price = price.replace(" ",""); // удаляет пробелы
                price = price.replace(" ","");
                var fulllength = price.length;
                var first = price.substr(0,fulllength-3); // строка до копеек
                var firstlenght = first.length;
                var second = price.substr(firstlenght);   // строка копеек
                first = first.replace(".","");    //удалние точки в рублёвой части строки 
                var newprice = first+second;    // склеиванье обратно в целую строку
                return $(o).text(newprice);                              
            }
            var status;
            // ф-я проверки данных яйчеек на FLOAT перед отпракой в БД
            function priceCheck(o){
                
                var price = $(o).text();
                var correctPrice = parseFloat(price);    // страрается привести к float если неудачно - возвращает NaN
                //return $(o).text(price);
                
                // прверяет значение на Nan
                if (isNaN(correctPrice) == true) {
            	   $('#resultMsg').addClass("fail").html("неверное значние в графе цен");  // вывод сообщения об ошибке
                   //correctPrice = newprice;  возвращение оргинального значения
                   return $(o).text(price).addClass('fail'); // подстветка некорректных данных и возаращение оригинальных данных в яйчейку (а не NaN)
                  // return status = "NO";
                   
                }else{              
                    return $(o).text(correctPrice).removeClass('fail');
                    //return status = "OK";

                } 
            }

            // фильтрация (пропуск чрезе функцию) колонок TotalPrice и UnitPrice по нажатию кнопки
            
            $('#filterTable').on('click', function(){
                $('#resultMsg').removeClass("fail").html("");// очистка сообщения об ошибке (если было)
                

                // если выбрано значение UnitPrice в выпадающем списке
                if ($(".sel option[value='UnitPrice']:selected")) {
                    // фильтрация колонки со значением UnitPrice
                    $(".sel option[value='UnitPrice']:selected").removeClass(); // удаляет все классы у выбранного элемента option (если ранее кнопка нажмалась и классы присвавалсь)
                    $(".sel option[value='UnitPrice']:selected").addClass('UnitPrice');
                    var UnitPrice_ColIndex = $('.UnitPrice').parent().parent().index();
                    //alert(UnitPrice_ColIndex); // отладка -  номер столбца с выбранным UnitPrice
                    $('tr:not(:lt(2))').map(function(i,el){ // игнорирует первые две строки (кнопка, выпадающй список)
                        return priceFilter( $('td:eq(' +  UnitPrice_ColIndex + ')', this) );
                    });
                }
                // если выбрано значение TotalPrice в выпадающем списке
                if ($(".sel option[value='TotalPrice']:selected")) {
                    // фильтрация колонки со значением TotalPrice
                    $(".sel option[value='TotalPrice']:selected").removeClass();
                    $(".sel option[value='TotalPrice']:selected").addClass('TotalPrice');   // проверка выбрана ли значене TotalPrice в выпадающем списке
                    var TotalPrice_ColIndex = $('.TotalPrice').parent().parent().index();
                    //alert(TotalPrice_ColIndex); // отладка -  номер столбца с выбранным TotalPrice
                    $('tr:not(:lt(2))').map(function(i,el){ // игнорирует первые две строки (кнопка, выпадающй список)
                        return priceFilter( $('td:eq(' +  TotalPrice_ColIndex + ')', this) );
                    });
                }
            });
            
    /////////////////////////////////////////////////////////////////////////////////
    //  подготовка и отправка даннных в PHP
    ////////////////////////////////////////////////////////////////////////////////
        // отменяет стандартную отправку формы
        $("#frm").submit(function(){
            return false;
        });
        
        // кнопка отправки
        $("#btnSave").on('click',function(){
            $('#resultMsg').removeClass("fail").html("");// очистка сообщения об ошибке (если было)            
                
                // проверка значений яйчеек на FLOAT перед отпраавкой
                
                // если выбрано значение UnitPrice в выпадающем списке
                if ($(".sel option[value='UnitPrice']:selected")) {
                    // фильтрация колонки со значением UnitPrice
                    $(".sel option[value='UnitPrice']:selected").removeClass(); // удаляет все классы у выбранного элемента option (если ранее кнопка нажмалась и классы присвавалсь)
                    $(".sel option[value='UnitPrice']:selected").addClass('UnitPrice');
                    var UnitPrice_ColIndex = $('.UnitPrice').parent().parent().index();
                    //alert(UnitPrice_ColIndex); // отладка -  номер столбца с выбранным UnitPrice
                    $('tr:not(:lt(2))').map(function(i,el){ // игнорирует первые две строки (кнопка, выпадающй список)
                        return priceCheck( $('td:eq(' +  UnitPrice_ColIndex + ')', this) );
                    });
                }
                // если выбрано значение TotalPrice в выпадающем списке
                if ($(".sel option[value='TotalPrice']:selected")) {
                    // фильтрация колонки со значением TotalPrice
                    $(".sel option[value='TotalPrice']:selected").removeClass();
                    $(".sel option[value='TotalPrice']:selected").addClass('TotalPrice');   // проверка выбрана ли значене TotalPrice в выпадающем списке
                    var TotalPrice_ColIndex = $('.TotalPrice').parent().parent().index();
                    //alert(TotalPrice_ColIndex); // отладка -  номер столбца с выбранным TotalPrice
                    $('tr:not(:lt(2))').map(function(i,el){ // игнорирует первые две строки (кнопка, выпадающй список)
                        return priceCheck( $('td:eq(' +  TotalPrice_ColIndex + ')', this) );
                    });
                }
            // если все ценове яйчейки прошли проверку на FLOAT - сбор данных и отпрака в PHP
            if ($('#resultMsg').hasClass("fail")) {
                return;
            }else{
                                                                   
                // имя таблцы из формы
                var tableName = $('#tableName').val();
                
                // главный массив
                var allVal = new Array;
                // обход всей таблицы (сключая первые 2 строки с кнопками))
                $('tr:not(:lt(2))').map(function(i,tr){
                    return allVal[i] = $('td',tr).map(function(k,td){
                        return $(td).text();
                    }).get();
                });
                // информационый подмассив для имени таблицы и названиями колонок
                var allVal_Z = new Array;
                // заполнениние информационого подмаисва
                allVal_Z.push(tableName);
                allVal_Z.push("productName");
                allVal_Z.push("Quantity");
                allVal_Z.push("UnitPrice");
                allVal_Z.push("TotalPrice");
                allVal_Z.push("Project");      
                // вставка информационого подмаисва в главнный массив
                allVal.push(allVal_Z);                
                // упакова в json-строку
                var strVals = JSON.stringify(allVal);
                
                
                //alert(allVal_Z[1]);
                // отправка постом  
                $.post('toSQL.php', strVals, function(json){
        			// проверка значения возвращаемая сервером
        			
                    if (json.status == "fail") {
        				$('#resultMsg').addClass("fail").html(json.message);
        			}
        			if (json.status == "success") {
        				$('button').attr('disabled','true');   // отключение кнопок
                        $('#resultMsg').addClass("success").html(json.message);
        			}
        		},"json");
                
            }
                
        });
         
    });
     </script>
</head>
<body >
<a href="index.html">&#60;&#60; назад</a>

        <h3>Шаг 2:</h3>
        <p style="font-size: 12px;">
        1) Удалите ненужные строки и столбцы<br/>
        2) Отредактируйте значения яйчеек таблицы вручную<br/>
        3) Сопоставьте столбцы для вливания в БД<br/>
        4) Нажмите кнопку для автоматической фильтрации колонок с ценами<br/> 
        5) нажмите кнопку отправки в БД
        </p>
        <form id="frm" method="post" action="toSQL.php">
            Имя таблцы для базы данных&nbsp;<input type="text" name="tableName" id="tableName"  size="20" />&nbsp;<span style="font-size: 12px;">(латинскими буквами без пробелов)</span><br />
            <br />
            <button type="submit" name="filterTable" id="filterTable">Отфильтровать колонки с ценами</button><br />
            <button type="submit" name="btnSave" id="btnSave" style="font-size: 17px;">ОТПРАВИТЬ В БД</button>
            <span id="resultMsg"></span>
            <input type="hidden" name="action" value="addRunner" id="action" />
        </form>



<br />
<?php
require "excelparser.php";
////td.dt_string {font-size: 10px; color: #000090; font-weight: bold}

function getmicrotime() {
    list($usec, $sec) = explode(" ",microtime());
    return ((float)$usec + (float)$sec);
}

function uc2html($str) {
	$ret = '';
	for( $i=0; $i<strlen($str)/2; $i++ ) {
		$charcode = ord($str[$i*2])+256*ord($str[$i*2+1]);
		$ret .= '&#'.$charcode;
	}
	return $ret;
}

function show_time() {
	global $time_start,$time_end;

	$time = $time_end - $time_start;
	echo "Анализ сделан за $time секунды<hr size=1><br>";
}

function fatal($msg = '') {
	echo '[Fatal error]';
	if( strlen($msg) > 0 )
		echo ": $msg";
	echo "<br>\nВыполнение Скрипта прервано <br>\n";
	if( $f_opened) @fclose($fh);
	exit();
};

$err_corr = "Неподдерживаемый формат или битый файл";

$excel_file_size;
$excel_file = $_FILES['excel_file'];
if( $excel_file )
	$excel_file = $_FILES['excel_file']['tmp_name'];

if( $excel_file == '' ) fatal("Файл не загружен");

$exc = new ExcelFileParser("debug.log", ABC_NO_LOG );//ABC_VAR_DUMP);

$style = $_POST['style'];
if( $style == 'old' )
{
	$fh = @fopen ($excel_file,'rb');
	if( !$fh ) fatal("Файл не загружен");
	if( filesize($excel_file)==0 ) fatal("Файл не загружен");
	$fc = fread( $fh, filesize($excel_file) );
	@fclose($fh);
	if( strlen($fc) < filesize($excel_file) )
		fatal("Немогу прочитать файл");
		
	$time_start = getmicrotime();
	$res = $exc->ParseFromString($fc);
	$time_end = getmicrotime();
}
elseif( $style == 'segment' )
{
	$time_start = getmicrotime();
	$res = $exc->ParseFromFile($excel_file);
	$time_end = getmicrotime();
}

switch ($res) {
	case 0: break;
	case 1: fatal("Невозможно открыть файл");
	case 2: fatal("Файл, слишком маленький чтобы быть файлом Excel");
	case 3: fatal("Ошибка чтения заголовка файла");
	case 4: fatal("Ошибка чтения файла");
	case 5: fatal("Это - не файл Excel или файл, сохраненный в Excel < 5.0");
	case 6: fatal("Битый файл");
	case 7: fatal("В файле не найдены данные  Excel");
	case 8: fatal("Неподдерживаемая версия файла");

	default:
		fatal("Неизвестная ошибка");
}

/*
print '<pre>';
print_r( $exc );
print '</pre>';
exit;
*/


/*
// время обработки
show_time();
*/


/*
// легенда
echo <<<LEG
<b>Легенда:</b><br><br>
<table border=1 cellspacing=0 cellpadding=3>
<tr><td>Тип данных</td><td>Описание</td></tr>
<tr><td class=empty>&nbsp;</td><td class=index>Пустая ячейка</td></tr>
<tr><td class=dt_string>ABCabc</td><td class=index>Строка</td></tr>
<tr><td class=dt_int>12345</td><td class=index>Целое</td></tr>
<tr><td class=dt_float>123.45</td><td class=index>С плавающей запятой</td></tr>
<tr><td class=dt_date>123.45</td><td class=index>Дата</td></tr>
<table>
<br><br>

LEG;
*/
	for( $ws_num=0; $ws_num<count($exc->worksheet['name']); $ws_num++ )
	{
		/*
        // печать имя листа из excel
        print "<b>Рабочий лист: \"";
		if( $exc->worksheet['unicode'][$ws_num] ) {
			print uc2html($exc->worksheet['name'][$ws_num]);
		} else
			print $exc->worksheet['name'][$ws_num];

		print "\"</b>";
        */
		$ws = $exc->worksheet['data'][$ws_num];

		if( is_array($ws) &&
		    isset($ws['max_row']) && isset($ws['max_col']) ) {
		 echo "\n<table id='mainTable' border=1 cellspacing=0 cellpadding=2>\n";

		 print "<tr id='firstRow'><td>&nbsp;</td>\n";
		 for( $j=0; $j<=$ws['max_col']; $j++ ) {
			print "<td class=index>&nbsp;";
			if( $j>25 ) print chr((int)($j/26)+64);
			print chr(($j % 26) + 65)."&nbsp;</td>";
		 }

		 for( $i=0; $i<=$ws['max_row']; $i++ ) {
		  print "<tr><td>".($i+1)."</td>\n";
		  if(isset($ws['cell'][$i]) && is_array($ws['cell'][$i]) ) {
		   for( $j=0; $j<=$ws['max_col']; $j++ ) {

			if( ( is_array($ws['cell'][$i]) ) &&
			    ( isset($ws['cell'][$i][$j]) )
			   ){

			 // Печать данных ячейки
			 print "<td  contenteditable='true' class=\"";
			 $data = $ws['cell'][$i][$j];

			 $font = $ws['cell'][$i][$j]['font'];
			 $style = " style ='".ExcelFont::ExcelToCSS($exc->fonts[$font])."'";

		   switch ($data['type']) {
			// строка
			case 0:
				print "dt_string\"".$style.">";
				$ind = $data['data'];
				if( $exc->sst['unicode'][$ind] ) {
					$s = uc2html($exc->sst['data'][$ind]);
				} else
					$s = $exc->sst['data'][$ind];
				if( strlen(trim($s))==0 )
					print "&nbsp;";
				else
		print $s;
				break;
			//целое число
			case 1:

				print "dt_int\"".$style.">";
				print (int)($data['data']);
				break;
			//вещественное число
			case 2:

				print "dt_float\"".$style.">";
				print (float)($data['data']);
				break;
			// дата
			case 3:

				print "dt_date\"".$style.">";

				$ret = $exc->getDateArray($data['data']);
				printf ("%s-%s-%s",$ret['day'], $ret['month'], $ret['year']);
				break;
			default:
				print "dt_unknown\"".$style."> &nbsp;";
				break;
		   }
			 print "</td>\n";
			} else {
				print "<td contenteditable='true' class=empty>&nbsp;</td>\n";
			}
		   }
		  } else {
			// печать пустой записи
			for( $j=0; $j<=$ws['max_col']; $j++ )
				print "<td contenteditable='true' class=empty>&nbsp;</td>";
			print "\n";
		  }
		  print "</tr>\n";
		 }

		 echo "</table><br>\n";
		} else {
			// пустой рабочий лист
			print "<b> - Пусто</b><br>\n";
		}
		print "<br>";
	}

/*	print "Форматы<br>";
	foreach($exc->format as $value) {
		printf("( %x )",array_search($value,$exc->format));
		print htmlentities($value,ENT_QUOTES);
		print "<br>";
	}

    print "XFs<br>";
	for( $i=0;$i<count($exc->xf['format']);$i++) {
		printf ("(%x)",$i);
		printf (" Формат (%x) шрифт (%x)",$exc->xf['format'][$i],$exc->xf['font'][$i]);

		print "<br>";
	}
*/


?>

</body>
</html>
