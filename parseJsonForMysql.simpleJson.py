#!/usr/bin/env python
# -*- coding: utf-8 -*-

import sys, _mysql, MySQLdb, codecs, datetime, time, chardet
import json

if len( sys.argv ) != 3:
	print "\nUsage:", sys.argv[ 0 ], "<json file> <lang (hr, sr, fr, en, es, el, ...)>\n"
	exit()

f, lang = sys.argv[ 1: ]

def insertData( surface, candidate, con, count, topPos, lang, taskId ):
	
	con.set_character_set('utf8')
	c = con.cursor()
	c.execute( """SET NAMES %s""", ( u"utf8" ) )
	c.execute( """SET CHARACTER SET %s""", ( u"utf8" ) )
	c.execute( """SET character_set_connection=%s""", ( u"utf8" ) )

	if( len( candidate[ 'lemma' ] ) != len( candidate[ 'paradigm' ] ) or len( candidate[ 'lemma' ] ) != len( candidate[ 'expanded' ] ) 
		or len( candidate[ 'probability' ] ) != len( candidate[ 'lemma' ] ) ):
		print len( candidate[ 'lemma' ] ), len( candidate[ 'paradigm' ] ), len( candidate[ 'expanded' ] ), len( candidate[ 'probability' ] )
		print "bouh"
		return 0

	#c.execute( """SELECT id_surface FROM surface WHERE value_surface = %s AND lang_surface = %s COLLATE utf8_bin""", ( item[ 'surface_form' ], lang ) )
	#res = c.fetchone()

	#if not( res is None ):
	#	idSurface = res[ 0 ]
	#else:


	#print "INSERT INTO surface ( value_surface, lang_surface, xval_surface, top_pos_id, id_task ) VALUES ( %s, %s, %s, %s, %s )" % ( surface, lang, "0", topPos, taskId )
			

	c.execute( """INSERT INTO surface ( value_surface, lang_surface, xval_surface, top_pos_id, id_task ) VALUES ( %s, %s, %s, %s, %s )""", ( surface, lang, "0", topPos, taskId ) )
       	idSurface = c.lastrowid

	#print idSurface

	
	for i in range( len( candidate[ 'lemma' ] ) ):

		#c.execute( """SELECT id_lemma FROM lemma WHERE value_lemma = %s COLLATE utf8_bin""", ( candidate[ 'lemma' ][ i ] ) )
		#res = c.fetchone()

		#if not( res is None ):
		#	idLemma = res[ 0 ]
		#else:

		#print "INSERT INTO lemma ( value_lemma ) VALUES ( %s )" % ( candidate[ 'lemma' ][ i ] )
		c.execute( """INSERT INTO lemma ( value_lemma ) VALUES ( %s )""", ( candidate[ 'lemma' ][ i ] ) )
		idLemma = c.lastrowid

		#c.execute( """SELECT id_paradigm FROM paradigm WHERE value_paradigm = %s COLLATE utf8_bin""", ( candidate[ 'paradigm' ][ i ][ 0 ] ) )
		#res = c.fetchone()
		#if not( res is None ):
		#	idParadigm = res[ 0 ]
		#else:
		#print "INSERT INTO paradigm ( value_paradigm, id_pos ) VALUES ( %s, %s )" % ( candidate[ 'paradigm' ][ i ][ 0 ], candidate[ 'paradigm' ][ i ][ 1 ] )
		c.execute( """INSERT INTO paradigm ( value_paradigm, id_pos ) VALUES ( %s, %s )""", ( candidate[ 'paradigm' ][ i ][ 0 ], candidate[ 'paradigm' ][ i ][ 1 ] ) )
		idParadigm = c.lastrowid

		tmpExpanded = "::".join( [ "__".join( item ) for item in candidate[ 'expanded' ][ i ] ] )
		#print "INSERT INTO expanded ( value_expanded ) VALUES ( %s )" % ( tmpExpanded )
		c.execute( """INSERT INTO expanded ( value_expanded ) VALUES ( %s )""", ( tmpExpanded ) );
		idExpanded = c.lastrowid

		#print "INSERT INTO candidate ( id_surface, id_lemma, id_paradigm, id_expanded, id_pos, probability ) VALUES ( %s, %s, %s, %s, %s, %s )" % ( idSurface, idLemma, idParadigm, idExpanded, candidate[ 'paradigm' ][ i ][ 1 ], round( float( candidate[ 'probability' ][ i ] ), 6 ) )

		c.execute( """INSERT INTO candidate ( id_surface, id_lemma, id_paradigm, id_expanded, id_pos, probability ) VALUES ( %s, %s, %s, %s, %s, %s )""", ( idSurface, idLemma, idParadigm, idExpanded, candidate[ 'paradigm' ][ i ][ 1 ], round( float( candidate[ 'probability' ][ i ] ), 6 ) ) )
		idCandidate = c.lastrowid

		#print idCandidate

	con.commit()


def getLangId( con, lang ):
	idLang = -1
	c = con.cursor()
	c.execute( """SELECT id_lang FROM lang WHERE shortname_lang = %s COLLATE utf8_bin""", ( lang ) )
	res = c.fetchone()
	if not( res is None ):
		idLang = res[ 0 ]
	else:
		c.execute( """INSERT INTO lang ( shortname_lang ) VALUES ( %s )""", ( lang ) )
		idLang = c.lastrowid
	con.commit()
	return idLang

def insertNewTask( con, lang ):
	d = datetime.datetime.fromtimestamp( time.time() )
	d = d.strftime( '%Y%m%d%H%M%S' )
	c = con.cursor()
	c.execute( """INSERT INTO task ( id_lang, date_create, activate_task ) VALUES ( %s, %s, %s )""", ( lang, d, 1 ) );
	return c.lastrowid


def parseJson( f, con, lang ):
	handler = codecs.open( f, 'r', encoding='utf8' )
	content = handler.readlines()
	content = " ".join( content )
	j = json.loads( content )
	surface = ''
	candidate = {}
	count = 0
	topProba = 0.0
	topPos = "1"
	lang = getLangId( con, lang )
	lang = unicode( str( lang ), "utf8" )
	taskId = insertNewTask( con, lang )
	for item in j:
		surface = item[ 'surface_form' ]
		for cand in item[ 'candidates' ]:
			for cont in cand:
				if cont not in candidate:
					candidate[ cont ] = []
				if cont == 'paradigm':
					tmpPos = cand[ cont ].split( '__' )[ -1 ]
					if tmpPos[ 0 ] == 'n':
						postype = 1
					elif tmpPos[ 0 ] == 'a':
						postype = 2
					elif tmpPos[ 0 ] == 'v':
						postype = 3
					candidate[ cont ].append( ( cand[ cont ], postype ) )
				else:
					if cont == 'probability':
						if round( float( cand[ cont ] ), 6 ) > round( float( topProba), 6 ):
							topProba = round( float( cand[ cont ] ), 6 )
							topPos = postype
					candidate[ cont ].append( cand[ cont ] )
	
		insertData( surface, candidate, con, count, topPos, lang, taskId )
		topProba = 0.0
		surface = ''
	        candidate = {}
		topPos = "1"

con = 0

try:
	con = MySQLdb.connect( host='localhost', user='paradigm_admin', passwd='paradigmadmin', db='paradigm', charset='utf8', init_command='SET NAMES UTF8', use_unicode=True )
	
	parseJson( f, con, lang )
except _mysql.Error, e:
  
	print "Error %d: %s" % (e.args[0], e.args[1])
	sys.exit(1)

finally:
    
	if con:
		con.close()


