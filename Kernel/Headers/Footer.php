<?
if(defined('DEBUG') AND DEBUG == true)
{
	echo '<div class="wrapper" style="font-size: 11px; margin-top: 20px; line-height: 15px;"><hr /><br />';
	echo BitRock::Statistics();
	echo '<br />* Esta información aparece debido a que tiene definida la constante DEBUG en Init.php';
	echo '</div>';
}	

if($site['site_bottom_javascript'] == "true")
	echo '<!-- JavaScript -->' . Tpl::$js;
	
if(!empty(Tpl::$javascript))
{
	echo Tpl::$javascript; 
	echo ' })</script>'; 
} 
?>
</div>

</body>
</html>