<?php
/**
 * @desc ϵͳͨ�õĴ������ͼ��������塣�������෢������ʱ���ɼ̳д��࣬
 * Ȼ���ٴ������������(1000)֮����в��䣬�������ҪҲ���Ը��Ǵ���Ķ��塣
 * ����ĳ�Ա�ͷ���������Ϊstatic����Ϊ��ϵͳ����ʱ�������ж��ʵ�����ڣ�
 * Ҳ������ζ�ŵ����󲻼�ϵķ����󣬺����ĻḲ��ǰ���
 * @author Administrator
 *
 */
require_once dirname(__FILE__).'/IError.php';
class CError implements IError
{
	CONST DEBUG_ON = true;
	CONST DEBUG_OFF = false;
	
	CONST RAISED_ERROR_OVERRIDE_ON = true;
	CONST RAISED_ERROR_OVERRIDE_OFF = false;
	
	CONST COMMON_MAX_CODE = 1000;
	
	//paramter error
	CONST COMMON_UNKNOWN = 0;
	
	CONST COMMON_PARAM_NUM = 1;
	CONST COMMON_PARAM_TYPE = 2;
	CONST COMMON_PARAM_EMPTY = 3;
	
	//file error
	CONST COMMON_FILE_TYPE = 100;
	CONST COMMON_FILE_EXISTS = 101;
	CONST COMMON_FILE_NOT_EXISTS = 102;
	CONST COMMON_FILE_UNABLE_WRITE = 103;
	CONST COMMON_FILE_UNABLE_READ = 104;
	CONST COMMON_FILE_UPLOAD = 105; 
	
	CONST COMMON_FORM_FIELD_EMPTY = 200;
	CONST COMMON_FORM_FIELD_TYPE = 201;
	CONST COMMON_FORM_FIELD_NUM = 202;
	
	CONST COMMON_CHARSET_NOT_EXISTS = 300;
	
	CONST COMMON_FUNC_RETURN_TYPE = 400;
	CONST COMMON_FUNC_RETURN_EMPTY = 401;
	
	CONST COMMON_DECLARATION_EXISTS = 500;
	CONST COMMON_DECLARATION_NOT_EXISTS = 501;
	CONST COMMON_INTERFACE_NOT_IMPLEMENT = 502;
	
	CONST COMMON_REPEAT_EXISTS = 600;
	CONST COMMON_OVERFLOW = 610;
	
	CONST COMMON_DB_EXCEPTION = 700;
	
	CONST COMMON_CACHE_EXCEPTION = 800;
	
	protected static $curErrorCode = 0;
	protected static $sCurErrorDetail = '����';
	protected static $sCurDebugTrace = '';
	
	/**
	 * @desc ��һ�������ĵ��ô�����ʱ���п�����ײ�Ĵ���ֻ�ж���
	 * �Ż���д�����ʱ������������оͿ���ʹ��raiseError��������
	 * ������㴫�ݶ�������
	 * @var object
	 */
	protected static $oRaisedError = null;
	
	protected static $arrErrorDesc = array(
		self::COMMON_UNKNOWN                 => 'δ֪����'
		
		,self::COMMON_PARAM_NUM              => '������������'
		,self::COMMON_PARAM_TYPE             => '������������'
		,self::COMMON_PARAM_EMPTY            => '��������Ϊ��'
		
		,self::COMMON_FILE_TYPE              => '�ļ���������'
		,self::COMMON_FILE_EXISTS            => '�ļ��Ѿ�����'
		,self::COMMON_FILE_NOT_EXISTS        => '�ļ�������'
		,self::COMMON_FILE_UNABLE_WRITE      => '�ļ��޷�д��'
		,self::COMMON_FILE_UNABLE_READ       => '�ļ��޷���ȡ'
		,self::COMMON_FILE_UPLOAD            => '�ļ��ϴ�����'
		
		,self::COMMON_FORM_FIELD_EMPTY       => '���ֶ�Ϊ��'
		,self::COMMON_FORM_FIELD_TYPE        => '���ֶ�����'
		,self::COMMON_FORM_FIELD_NUM         => '���ֶ���Ŀ'
		
		,self::COMMON_CHARSET_NOT_EXISTS     => '�����ڴ��ַ������ַ���������'
		
		,self::COMMON_FUNC_RETURN_TYPE       => '��������ֵ��������'
		,self::COMMON_FUNC_RETURN_EMPTY      => '��������ֵΪ��'
		
		,self::COMMON_DECLARATION_EXISTS     => '����(����)�Ѿ�����'
		,self::COMMON_DECLARATION_NOT_EXISTS => '����(����)������'
		,self::COMMON_INTERFACE_NOT_IMPLEMENT => '�ӿ�δʵ��'
		
		,self::COMMON_REPEAT_EXISTS          => '�����ظ���ֵ '
		,self::COMMON_OVERFLOW               => '��������������ֵ '
		
		,self::COMMON_DB_EXCEPTION           => '���ݿ�����ʧ�ܣ����Ժ����� '
		,self::COMMON_CACHE_EXCEPTION        => 'CACHE����ʧ�ܣ����Ժ����� '
		
	);
		
	private static $bDebug = self::DEBUG_OFF;
	
	static function setDebug($b = self::DEBUG_OFF)
	{
		self::$bDebug = $b;
	}
	
	/**
	 * @desc ������������ϴ���
	 * @param object $obj CError��һ���������
	 * @param bool $bOverride �Ƿ񸲸�ǰ��һ���������, �����,����Խ���һ����������Ϊnull
	 */
	static function raiseError($obj=null, $bOverride=self::RAISED_ERROR_OVERRIDE_ON)
	{
		if($obj && $bOverride == self::RAISED_ERROR_OVERRIDE_ON)
			self::$oRaisedError = $obj;
	}
	
	static function clearRaisedError()
	{
		self::$oRaisedError = null;
	}
	
	/**
	 * @desc ����һ������
	 * @param int $code �������
	 * @param string $detail ����Ĳ���˵��
	 * @return no return`
	 */
	static function setError($code=self::COMMON_UNKNOWN, $detail='')
	{
		self::$curErrorCode = $code;
		self::$sCurErrorDetail = $detail;
		self::$sCurDebugTrace = '';
		if(self::$bDebug)
		{
			self::$sCurDebugTrace = IS_CLI ? "\n".var_export(debug_backtrace(), true) 
				: "<br/>".str_replace(array("\n"), array('<br/>'), var_export(debug_backtrace(), true));
		}
	}
	
	static function getErrorCode()
	{
		return self::$curErrorCode;
	}
	
	static function getErrorDesc()
	{
		return self::$arrErrorDesc[self::$curErrorCode] .' , '. self::$sCurErrorDetail.self::$sCurDebugTrace;
	}
}

?>