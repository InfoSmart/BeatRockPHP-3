<?
class Messages
{
	static function SendRandom($conn)
	{
		$random = Core::Random(20);
		$conn->Send($random);
	}

	static function SendUserID($conn)
	{
		$id = 1;
		$conn->Send('YOURID+' . $id);
	}
}
?>