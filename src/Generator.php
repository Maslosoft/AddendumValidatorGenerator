<?php

namespace Maslosoft\AddendumValidatorGenerator;

use CFileHelper;
use CValidator;
use Maslosoft\Addendum\Annotations\TargetAnnotation;
use Maslosoft\Addendum\Base\ValidatorAnnotation;
use Maslosoft\Addendum\Builder\DocComment;
use Maslosoft\Addendum\Helpers\MiniView;
use Maslosoft\Addendum\Matcher\AnnotationsMatcher;
use Maslosoft\Addendum\Reflection\ReflectionAnnotatedClass;
use ReflectionClass;
use ReflectionProperty;
use RuntimeException;
use Yii;
use ZipArchive;

class Generator
{

	/**
	 * Generate validator annotations from existing validator classes
	 */
	public function generate()
	{
		$v = CValidator::$builtInValidators;
		$template = <<<CODE
<?php
/**
 * NOTE: This class is automatically generated from Yii validator class.
 * This is not actual validator. For validator class @see C%2\$s.
 */
%1\$s
class %2\$sAnnotation extends EValidatorAnnotation implements IBuiltInValidatorAnnotation
{
%3\$s
}
CODE;
		$ignored = [];
		$info = new ReflectionClass(ValidatorAnnotation::class);
		foreach ($info->getProperties(ReflectionProperty::IS_PUBLIC) as $field)
		{
			$ignored[$field->name] = $field->name;
		}
		$ignored['attributes'] = true;
		$ignored['builtInValidators'] = true;
		foreach ($v as $n => $class)
		{
			$name = ucfirst($n) . 'Validator';
			$info = new ReflectionAnnotatedClass($class);
			$classComment = $info->getDocComment();
			$values = $info->getDefaultProperties();
			$fields = [];
			foreach ($info->getProperties(ReflectionProperty::IS_PUBLIC) as $field)
			{
				if (isset($ignored[$field->name]))
				{
					continue;
				}
				$comment = $field->getDocComment();
				$fields[$field->name] = sprintf("\t%s\n\tpublic \$%s = %s;\n", $comment, $field->name, var_export($values[$field->name], true));
			}
			$code = sprintf($template, $classComment, $name, implode("\n", $fields));
			file_put_contents("c:/temp/{$name}Annotation.php", $code);
		}
	}

}
