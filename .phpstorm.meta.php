<?php
	namespace PHPSTORM_META {
	/** @noinspection PhpUnusedLocalVariableInspection */
	/** @noinspection PhpIllegalArrayKeyTypeInspection */
	$STATIC_METHOD_TYPES = [

		\D('') => [
			'Adv' instanceof Think\Model\AdvModel,
			'Mongo' instanceof Think\Model\MongoModel,
			'Auto' instanceof Admin\Model\AutoModel,
			'View' instanceof Think\Model\ViewModel,
			'Tool' instanceof Admin\Model\ToolModel,
			'Relation' instanceof Think\Model\RelationModel,
			'Lostpass' instanceof Admin\Model\LostpassModel,
			'Dept' instanceof Admin\Model\DeptModel,
			'User' instanceof Admin\Model\UserModel,
			'Validate' instanceof Admin\Model\ValidateModel,
			'City' instanceof Admin\Model\CityModel,
			'Merge' instanceof Think\Model\MergeModel,
		],
	];
}