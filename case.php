<?php
include 'CurlWrapper.class.php';

#######################################################
#################  WRAPPER METHODS  ###################
#######################################################
#       method      |              params             #
#-------------------+---------------------------------#
# setUrl            | $url (string)                   #
#-------------------+---------------------------------#
# setAuth           | $login (string),                #
#                   | $password (string)              #
#-------------------+---------------------------------#
# setGet            | **paramsArray                   #
#-------------------+---------------------------------#
# setPost           | **paramsArray                   #
#-------------------+---------------------------------#
# setCookie         | **paramsArray                   #
#-------------------+---------------------------------#
# cookieFile        | $fileName (string)              #
#                   |   (create in /tmp dir)          #
#-------------------+---------------------------------#
# setTimeout        | $timeout (int) sec              #
#-------------------+---------------------------------#
# setHeaderFields   | **paramsArray                   #
#-------------------+---------------------------------#
# setFile           | $fullFileName (string),         #
#                   | $headerFieldName (string)       #
#-------------------+---------------------------------#
# setAdditionalOpts | **paramsArray                   #
#-------------------+---------------------------------#
# allowRedirect     | $allowRedirect (bool)           #
#                   | $maxRedirectCount (int)         #
#-------------------+---------------------------------#
# setReferer        | $referer (string)               #
#-------------------+---------------------------------#
# run               | $reqSignature (string),         #
#                   | $isDisplayed (bool),            #
#-------------------+---------------------------------#
# getLog            | $requestSignature (string)      #
#                   |   (all requests by some sign.)  #
#                   | if empty - all requests         #
#=====================================================#
#                               #
# **paramsArray  =  array (     #
#     key => 'value',           #
#     ...                       #
# )                             #
#===============================#

########### WRITE YOUR TEST UNDER THIS ENTRY ###########


