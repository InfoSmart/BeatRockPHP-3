<?
#####################################################
## 					 BeatRock				   	   ##
#####################################################
## Framework avanzado de procesamiento para PHP.   ##
#####################################################
## InfoSmart © 2013 Todos los derechos reservados. ##
## http://www.infosmart.mx/						   ##
#####################################################
## http://beatrock.infosmart.mx/				   ##
#####################################################

# Acción ilegal.
if( !defined('BEATROCK') )
	exit;
	
class Zip extends Base
{
	public $file 	= '';
	public $zip 	= null;

	###############################################################
	## Lanzar error.
	## - $code:			Código del error.
	## - $message: 		Mensaje del error.
	###############################################################
	function Error($code, $message = '')
	{
		if( empty($message) AND $this->zip !== null )
			$message = $this->zip->getStatusString();

		parent::Error($code, $message);
	}

	###############################################################
	## Guarda los cambios hechos.
	###############################################################
	function Save()
	{
		if( $this->zip == null )
			return;

		$this->zip->close();
		$this->zip = null;
	}

	###############################################################
	## Constructor
	###############################################################
	function __construct($file)
	{
		$this->file = $file;
		return $this;
	}

	###############################################################
	## Prepara y obtiene la instancia ZIP.
	###############################################################
	function Prepare()
	{
		if( $this->zip !== null )
			return $this->zip;

		$zip = new ZipArchive;

		if( !file_exists($this->file) )
			$open = $zip->open($this->file, ZipArchive::CREATE | ZipArchive::OVERWRITE);
		else
			$open = $zip->open($this->file);

		if( $open !== true )
			$this->Error('zip.open');

		$this->zip = $zip;
		return $zip;
	}

	###############################################################
	## Extraer el contenido del archivo ZIP.
	## - $dir: 							Ruta del directorio donde extraer los archivos.
	## - $filename (string, array): 	Archivo(s) a extraer.
	###############################################################
	function Extract($dir, $filename = null)
	{
		Lang::SetSection('helper.zip');

		$zip 	= $this->Prepare();
		$result = $zip->extractTo($dir, $filename);
		
		if( !$result )
		{
			Reg('%error.extract%' . $file, 'error');
			$this->Error('zip.extract');

			return false;
		}
			
		Reg('%extract.correct%' . $file);
		return true;
	}
	
	###############################################################
	## Agregar un archivo al ZIP.
	## - $files (array): 		Archivo(s) a agregar.
	## - $localname: 			Ruta de destino en el ZIP.
	###############################################################
	function Add($files, $localname = '')
	{
		Lang::SetSection('helper.zip');
					
		$zip 	= $this->Prepare();
		$result	= true;
		
		if( is_array($files) )
		{
			foreach( $files as $file )
				$zip->addFile($file);
		}
		else
			$result = $zip->addFile($files, $localname);
		
		Reg('%add.correct%' . $file);
		return $result;	
	}

	###############################################################
	## Agregar un archivo mediante su contenido.
	## - $filename: Nombre del archivo.
	## - $data: 	Contenido del archivo.
	###############################################################
	function AddData($filename, $data = '')
	{
		$zip 	= $this->Prepare();
		$result = true;

		if( is_array($filename) )
		{
			foreach( $filename as $name => $data )
				$zip->addFromString($name, $data);
		}
		else
			$result = $zip->addFromString($filename, $data);

		return $result;
	}

	###############################################################
	## Agregar un directorio vacio.
	## - $path: Ruta del directorio.
	###############################################################
	function AddEmptyDir($path)
	{
		$zip = $this->Prepare();
		return $zip->addEmptyDir($path);
	}

	###############################################################
	## Agregar los archivos de un directorio.
	## - $dir: 		Ruta del directorio local.
	## - $zipdir: 	Ruta del directorio en el ZIP.
	###############################################################
	function AddDir($dir, $zipdir)
	{
		$zip 	= $this->Prepare();
		$files 	= Io::GetDirFiles($dir);

		if( strlen($zipdir) > 1 AND substr($zipdir, -1) == DS )
			$this->AddEmptyDir(substr($zipdir . $file, 0, strlen($zipdir . $file) - 1));
		else
			$this->AddEmptyDir($zipdir);

		foreach( $files as $file )
		{
			if( is_dir($dir . $file) )
			{
				$file 	= str_ireplace('/', '', $file) . DS;
				$zipdir = str_ireplace('/', '', $zipdir);
				
				$this->AddDir($dir . $file, $zipdir . $file);
			}
			else
				$this->Add($dir . $file, $zipdir . $file);
		}

		return true;
	}

	###############################################################
	## Eliminar un archivo.
	## - $path: Ruta del archivo.
	###############################################################
	function Delete($path)
	{
		$zip 	= $this->Prepare();
		$result = true;

		if( is_array($path) )
		{
			foreach( $path as $filename )
				$zip->deleteName($filename);
		}
		else
			$result = $zip->deleteName($path);

		return $result;
	}

	###############################################################
	## Obtener el contenido de un archivo.
	## - $filename: 	Ruta del archivo.
	## - $out:			Ruta donde guardar el archivo.
	###############################################################
	function Read($filename, $out = '')
	{
		$zip 	= $this->Prepare();
		$result = $zip->getFromName($filename);

		if( $result !== false AND !empty($out) )
			Io::Write($out, $result);

		return $result;
	}

	###############################################################
	## Renombrar un archivo.
	## - $filename: Ruta del archivo.
	## - $newname: 	Ruta con el nuevo nombre.
	###############################################################
	function Rename($filename, $newname)
	{
		$zip 	= $this->Prepare();
		return $zip->renameName($filename, $newname);
	}
}
?>