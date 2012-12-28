<?
#####################################################
## 					 BeatRock				   	   ##
#####################################################
## Framework avanzado de procesamiento para PHP.   ##
#####################################################
## InfoSmart © 2012 Todos los derechos reservados. ##
## http://www.infosmart.mx/						   ##
#####################################################
## http://beatrock.infosmart.mx/				   ##
#####################################################

// Acción ilegal.
if(!defined('BEATROCK'))
	exit;
	
class Zip
{
	public $file 	= '';
	public $zip 	= null;

	// Lanzar error.
	// - $code: Código del error.
	// - $function: Función causante.
	// - $message: Mensaje del error.
	function Error($code, $function, $file = '', $message = '')
	{
		if(empty($message) AND $this->zip !== null)
			$message = $this->zip->getStatusString();

		Bit::Status($message, __FILE__, ['function' => $function, 'file' => $file]);
		Bit::LaunchError($code);
	}

	// Guardar los cambios hechos.
	function Save()
	{
		if($this->zip == null)
			return;

		$this->zip->close();
		$this->zip = null;
	}

	// Constructor
	function __construct($file)
	{
		$this->file = $file;
		return $this;
	}

	// Preparar y obtener la instancia Zip.
	function Prepare()
	{
		if($this->zip !== null)
			return $this->zip;

		$zip 	= new ZipArchive;

		if(!file_exists($this->file))
			$open = $zip->open($this->file, ZipArchive::CREATE | ZipArchive::OVERWRITE);
		else
			$open = $zip->open($this->file);

		if($open !== true)
			$this->Error('zip.open', __FUNCTION__, $this->file);

		$this->zip = $zip;
		return $zip;
	}

	// Extraer el contenido del archivo ZIP.
	// - $dir: Ruta del directorio local donde extraer los archivos.
	// - $filename (string,array): Archivo(s) a extraer.
	function Extract($dir, $filename = null)
	{
		Lang::SetSection('mod.zip');

		$zip 	= $this->Prepare();
		$result = $zip->extractTo($dir, $filename);
		
		if(!$result)
		{
			Reg('%error.extract%' . $file, 'error');
			$this->Error();
			return false;
		}
			
		Reg('%extract.correct%' . $file);
		return true;
	}
	
	// Agregar un archivo.
	// - $files (Array): Archivo(s) a agregar.
	// - $localname: Nombre - Ruta del archivo en el ZIP.
	function Add($files, $localname = '')
	{
		Lang::SetSection('mod.zip');
					
		$zip 	= $this->Prepare();
		$result	= true;
		
		if(is_array($files))
		{
			foreach($files as $file)
				$zip->addFile($file);
		}
		else
			$result = $zip->addFile($files, $localname);
		
		Reg('%add.correct%' . $file);
		return true;	
	}

	// Agregar un archivo mediante su contenido.
	// - $filename: Nombre del archivo.
	// - $data: Contenido del archivo.
	function AddData($filename, $data = '')
	{
		$zip 	= $this->Prepare();
		$result = true;

		if(is_array($filename))
		{
			foreach($filename as $name => $data)
				$zip->addFromString($name, $data);
		}
		else
			$result = $zip->addFromString($filename, $data);

		return $result;
	}

	// Agregar un directorio vacio.
	// - $name: Nombre - Ruta del directorio.
	function AddEmptyDir($name)
	{
		$zip 	= $this->Prepare();
		$result = $zip->addEmptyDir($name);

		return $result;
	}

	// Agregar los archivos de un directorio.
	// - $dir: Ruta del directorio local.
	// - $zipdir: Ruta del directorio en el ZIP.
	function AddDir($dir, $zipdir)
	{
		$zip 	= $this->Prepare();
		$files 	= Io::GetDirFiles($dir);

		_r($files);

		$this->AddEmptyDir($zipdir);

		foreach($files as $file)
		{
			if(is_dir($dir . $file))
				$this->AddDir($dir . $file, $zipdir . $file);
			else
				$this->Add($dir . DS . $file, $zipdir . DS . $file);
		}

		return true;
	}

	// Eliminar un archivo.
	// - $name: Nombre - Ruta del archivo.
	function Delete($name)
	{
		$zip 	= $this->Prepare();
		$result = true;

		if(is_array($name))
		{
			foreach($name as $filename)
				$zip->deleteName($filename);
		}
		else
			$result = $zip->deleteName($name);

		return $result;
	}

	// Obtener el contenido de un archivo.
	// - $filename: Nombre - Ruta del archivo.
	// - $out: Ruta donde guardar el archivo.
	function Read($filename, $out = '')
	{
		$zip 	= $this->Prepare();
		$result = $zip->getFromName($filename);

		if($result !== false AND !empty($out))
			Io::Write($out, $result);

		return $result;
	}

	// Renombrar un archivo.
	// - $filename: Nombre - Ruta del archivo.
	// - $newname: Nuevo Nombre - Ruta del archivo.
	function Rename($filename, $newname)
	{
		$zip 	= $this->Prepare();
		$result = $zip->renameName($filename, $newname);

		return $result;
	}
}
?>