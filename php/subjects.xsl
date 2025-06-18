<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet
    version="1.0"
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

  <!-- Output HTML5 -->
  <xsl:output method="html" encoding="UTF-8" indent="yes"/>

  <!-- Match the root <subjects> element -->
  <xsl:template match="/subjects">
    <html lang="en">
      <head>
        <meta charset="UTF-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1"/>
        <title>
          Subjects
          <xsl:if test="subject[1]">
            – <xsl:value-of select="subject[1]/faculty"/>
          </xsl:if>
        </title>
        <!-- Bootstrap CSS opcional -->
        <link
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          rel="stylesheet"
        />
      </head>
      <body class="container py-5">
        <header class="mb-4">
          <h1 class="h3">
            <xsl:choose>
              <xsl:when test="subject">
                Subjects from: 
                <strong><xsl:value-of select="subject[1]/faculty"/></strong>
              </xsl:when>
              <xsl:otherwise>
                No subjects jet
              </xsl:otherwise>
            </xsl:choose>
          </h1>
          <xsl:if test="subject">
            <p class="text-muted">
              University:
              <em><xsl:value-of select="subject[1]/university"/></em>
            </p>
          </xsl:if>
        </header>

        <main>
          <xsl:choose>
            <xsl:when test="subject">
              <ul class="list-group">
                <xsl:for-each select="subject">
                  <li class="list-group-item">
                    <div class="d-flex justify-content-between align-items-start">
                      <div>
                        <h5 class="mb-1">
                          <xsl:value-of select="name"/>
                        </h5>
                        <small class="text-secondary">
                          ID: <xsl:value-of select="id"/>
                        </small>
                      </div>
                    </div>
                  </li>
                </xsl:for-each>
              </ul>
            </xsl:when>
            <xsl:otherwise>
              <p class="lead">There are no Subject in this faculty.</p>
            </xsl:otherwise>
          </xsl:choose>
        </main>

      </body>
    </html>
  </xsl:template>

</xsl:stylesheet>
