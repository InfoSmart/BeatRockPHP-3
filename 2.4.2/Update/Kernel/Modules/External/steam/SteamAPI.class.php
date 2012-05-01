<?php
/*
 *      Copyright 2010 Rob McFadzean <rob.mcfadzean@gmail.com>
 *      
 *      Permission is hereby granted, free of charge, to any person obtaining a copy
 *      of this software and associated documentation files (the "Software"), to deal
 *      in the Software without restriction, including without limitation the rights
 *      to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *      copies of the Software, and to permit persons to whom the Software is
 *      furnished to do so, subject to the following conditions:
 *      
 *      The above copyright notice and this permission notice shall be included in
 *      all copies or substantial portions of the Software.
 *      
 *      THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *      IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *      FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 *      AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *      LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *      OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *      THE SOFTWARE.
 *      
 */

class SteamAPIException extends Exception { }
class SteamAPI {
	
	public $customURL;
	public $steamID64;

	public $me;
	public $gameList;
	
	/**
	 *  Sets the $steamID64 or CustomURL then retrieves the profile.
	 * @param int $id
	 * */
	public function __construct($id) 
	{
		if(is_numeric($id))
			$this->steamID64 = $id;
		else
			$this->customURL = strtolower($id);

		$this->me = $this->retrieveProfile();
		$this->gameList = $this->retrieveGames();
	}

	/**
	 *  Creates and then returns the url to the profiles.
	 *  @return string
	 * */
	public function baseUrl() 
	{
		if(empty($this->customURL))
			return 'http://steamcommunity.com/profiles/' . $this->steamID64;
		else
			return 'http://steamcommunity.com/id/' . $this->customURL;
	}
	
	/**
	 *  Retrieves all of the games owned by the user
	 * */
	private function retrieveGames() 
	{
		$url = $this->baseUrl() . '/games?xml=1';

		$data = file_get_contents($url);
		$data = str_ireplace('<![CDATA[', '', $data);
		$data = str_ireplace(']]>', '', $data);

		$gameData = new SimpleXMLElement($data);
		$gameData = get_object_vars($gameData);
		$gameData = get_object_vars($gameData['games']);
		$gameData = $gameData['game'];

		if(!empty($gameData['error']))
			return false;

		foreach($gameData as $param => $data)
		{
			if(is_object($data))
			{
				$gameData[$param] = get_object_vars($data);

				if(empty($gameData[$param]))
					unset($gameData[$param]);
			}
		}

		return $gameData;
	}

	/**
	 *  Retrieves all of the information found on the profile.
	 * */
	private function retrieveProfile() 
	{
		$url = $this->baseUrl() . '/?xml=1';

		$data = file_get_contents($url);
		$data = str_ireplace('<![CDATA[', '', $data);
		$data = str_ireplace(']]>', '', $data);

		$profileData = new SimpleXMLElement($data);
		$profileData = get_object_vars($profileData);

		if(!empty($profileData['error']))
			return false;

		$profileData['profileUrl'] = $this->baseUrl();

		foreach($profileData as $param => $data)
		{
			if(is_object($data))
			{
				$profileData[$param] = get_object_vars($data);

				if(empty($profileData[$param]))
					unset($profileData[$param]);

				if(is_array($profileData[$param]))
				{
					foreach($profileData[$param] as $param2 => $data2)
						$profileData[$param][$param2] = get_object_vars($data2);
				}
			}
			else
				$profileData[$param] = _c($data);
		}

		// Ajustes para el módulo Social.

		$profileData['id'] = $profileData['steamID64'];
		$profileData['username'] = $profileData['steamID'];
		$profileData['name'] = $profileData['realname'];
		$profileData['profile_image_url'] = $profileData['avatarFull'];

        return $profileData;
	}
}

?>
