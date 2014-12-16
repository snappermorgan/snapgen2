
<?xml version="1.0" encoding="UTF-8"?>

<xsl:stylesheet version="1.0"
xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:template match="/">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Response</title>
    <style type="text/css" media="all">
    /*<![CDATA[*/
      html, body {
        width: 800px;
      	font-family: arial, verdana, sans-serif, tahoma, helvetica;
      	margin: 0px auto;
      	padding: 0;
      	color: black;
      	text-align: center;
      	font-size: 12px;
        background: #fff;
      }
      div#main {
        width: 800px;
      	text-align: left;
        background: #fff;
      	font-size: 12px;
      }
      p{
        margin: 0;
        padding: 10px 35px;
        line-height: 18px;
      }
      span.name{
      	font-size: 14px;
      }
    /*]]>*/
    </style>
	</head>
    <body>
   <div id="main">
	<xsl:if test="response/lead_admin_link!=''">
		<p><a><xsl:attribute name="href"><xsl:value-of select="response/lead_admin_link"/></xsl:attribute>Lead Admin Link</a></p><br />
   </xsl:if>
   <xsl:choose>
		<xsl:when test="response/buyers != ''">
		          <xsl:for-each select="response/buyers/buyer">
		 			<p>
						<xsl:if test="logo!=''">
							<img><xsl:attribute name="src"><xsl:value-of select="logo"/></xsl:attribute></img><br />
					    </xsl:if>
						<span class="name"><strong><xsl:value-of select="company_name"/></strong></span><br />
						<xsl:value-of select="first_name"/>&#160;<xsl:value-of select="last_name"/><br />
						<xsl:value-of select="address"/><br />
			            <xsl:value-of select="city"/>,&#160;<xsl:value-of select="state"/>&#160;<xsl:value-of select="zip"/><br />
			            <strong>Phone:</strong>&#160;<xsl:value-of select="phone"/><br />
						<strong>Email:</strong>&#160;<xsl:value-of select="email"/><br />
				        <strong>Website:</strong>&#160;
					        <xsl:if test="website!=''">
						        <a><xsl:attribute name="href"><xsl:value-of select="website"/></xsl:attribute><xsl:value-of select="website"/></a>
					        </xsl:if>
					        <br />
						<strong>Hours Open:</strong>&#160;<xsl:value-of select="hours_open"/><br />
					    <strong>Company Founded:</strong>&#160;<xsl:value-of select="company_founded"/><br />
			            <strong>Offer:</strong>&#160;<xsl:value-of select="offer"/><br />
					</p>	
					<hr />
		          </xsl:for-each>
       </xsl:when> 
		    <xsl:otherwise>
		    	Thank you!
		    </xsl:otherwise>
		</xsl:choose>
    </div>
  </body>
  </html>
</xsl:template>
</xsl:stylesheet>
