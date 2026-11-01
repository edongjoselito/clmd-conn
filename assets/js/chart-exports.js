/* Local chart exports: pdfMake and JSZip are bundled with the application. */
(function () {
    'use strict';
    var ns = 'http://schemas.openxmlformats.org/';
    function xml(value) {
        // Control characters pasted into remarks would make the file unreadable.
        return String(value == null ? '' : value).replace(/[\x00-\x08\x0B\x0C\x0E-\x1F￾￿]/g, '').replace(/[&<>"']/g, function (c) {
            return {'&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&apos;'}[c];
        });
    }
    function save(blob, name) {
        var url = URL.createObjectURL(blob), a = document.createElement('a');
        a.href = url; a.download = name; document.body.appendChild(a); a.click(); a.remove();
        setTimeout(function () { URL.revokeObjectURL(url); }, 30000);
    }
    function rels(items) {
        return '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="' + ns + 'package/2006/relationships">' + items.map(function (r) {
            return '<Relationship Id="' + r[0] + '" Type="' + ns + 'officeDocument/2006/relationships/' + r[1] + '" Target="' + r[2] + '"/>';
        }).join('') + '</Relationships>';
    }
    function types(parts) {
        return '<?xml version="1.0" encoding="UTF-8"?><Types xmlns="' + ns + 'package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Default Extension="png" ContentType="image/png"/>' + parts.map(function (p) {
            return '<Override PartName="/' + p[0] + '" ContentType="application/vnd.openxmlformats-officedocument.' + p[1] + '+xml"/>';
        }).join('') + '</Types>';
    }
    function office(format, title, context, png, width, height, rows) {
        var zip = new JSZip(), w = 5486400, h = Math.round(w * height / width);
        // Bound images to a portrait page while preserving the complete chart.
        if (h > 7315200) { w = Math.round(w * 7315200 / h); h = 7315200; }
        if (format === 'docx') {
            zip.file('[Content_Types].xml', types([['word/document.xml','wordprocessingml.document.main']]));
            zip.file('_rels/.rels', rels([['rId1','officeDocument','word/document.xml']]));
            zip.file('word/_rels/document.xml.rels', rels([['rId1','image','media/chart.png']]));
            zip.file('word/media/chart.png', png.split(',')[1], {base64:true});
            function para(text) { return '<w:p><w:r><w:t xml:space="preserve">' + xml(text) + '</w:t></w:r></w:p>'; }
            zip.file('word/document.xml', '<?xml version="1.0" encoding="UTF-8"?><w:document xmlns:w="' + ns + 'wordprocessingml/2006/main" xmlns:r="' + ns + 'officeDocument/2006/relationships" xmlns:wp="' + ns + 'drawingml/2006/wordprocessingDrawing" xmlns:a="' + ns + 'drawingml/2006/main" xmlns:pic="' + ns + 'drawingml/2006/picture"><w:body>' + para(title) + para(context) + '<w:p><w:r><w:drawing><wp:inline><wp:extent cx="' + w + '" cy="' + h + '"/><wp:docPr id="1" name="Chart"/><a:graphic><a:graphicData uri="' + ns + 'drawingml/2006/picture"><pic:pic><pic:nvPicPr><pic:cNvPr id="1" name="Chart"/><pic:cNvPicPr/></pic:nvPicPr><pic:blipFill><a:blip r:embed="rId1"/><a:stretch><a:fillRect/></a:stretch></pic:blipFill><pic:spPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="' + w + '" cy="' + h + '"/></a:xfrm><a:prstGeom prst="rect"><a:avLst/></a:prstGeom></pic:spPr></pic:pic></a:graphicData></a:graphic></wp:inline></w:drawing></w:r></w:p><w:tbl>' + rows.map(function (row) { return '<w:tr>' + row.map(function (cell) { return '<w:tc>' + para(cell == null ? 'No data' : cell) + '</w:tc>'; }).join('') + '</w:tr>'; }).join('') + '</w:tbl><w:sectPr><w:pgSz w:w="12240" w:h="15840"/><w:pgMar w:top="720" w:right="720" w:bottom="720" w:left="720"/></w:sectPr></w:body></w:document>');
        } else {
            zip.file('[Content_Types].xml', types([['xl/workbook.xml','spreadsheetml.sheet.main'],['xl/worksheets/sheet1.xml','spreadsheetml.worksheet'],['xl/drawings/drawing1.xml','drawing']]));
            zip.file('_rels/.rels', rels([['rId1','officeDocument','xl/workbook.xml']]));
            zip.file('xl/workbook.xml', '<workbook xmlns="' + ns + 'spreadsheetml/2006/main" xmlns:r="' + ns + 'officeDocument/2006/relationships"><sheets><sheet name="Chart data" sheetId="1" r:id="rId1"/></sheets></workbook>');
            zip.file('xl/_rels/workbook.xml.rels', rels([['rId1','worksheet','worksheets/sheet1.xml']]));
            zip.file('xl/worksheets/_rels/sheet1.xml.rels', rels([['rId1','drawing','../drawings/drawing1.xml']]));
            var sheetRows = [[title],[context]].concat(rows);
            zip.file('xl/worksheets/sheet1.xml', '<worksheet xmlns="' + ns + 'spreadsheetml/2006/main" xmlns:r="' + ns + 'officeDocument/2006/relationships"><cols><col min="1" max="4" width="32" customWidth="1"/></cols><sheetData>' + sheetRows.map(function (row, i) {
                return '<row r="' + (i+1) + '">' + row.map(function (cell, j) {
                    var ref = String.fromCharCode(65+j) + (i+1);
                    return typeof cell === 'number' && isFinite(cell) ? '<c r="' + ref + '"><v>' + cell + '</v></c>' : '<c r="' + ref + '" t="inlineStr"><is><t xml:space="preserve">' + xml(cell == null ? 'No data' : cell) + '</t></is></c>';
                }).join('') + '</row>';
            }).join('') + '</sheetData><drawing r:id="rId1"/></worksheet>');
            zip.file('xl/media/chart.png', png.split(',')[1], {base64:true});
            zip.file('xl/drawings/_rels/drawing1.xml.rels', rels([['rId1','image','../media/chart.png']]));
            zip.file('xl/drawings/drawing1.xml', '<xdr:wsDr xmlns:xdr="' + ns + 'drawingml/2006/spreadsheetDrawing" xmlns:a="' + ns + 'drawingml/2006/main" xmlns:r="' + ns + 'officeDocument/2006/relationships"><xdr:oneCellAnchor><xdr:from><xdr:col>5</xdr:col><xdr:colOff>0</xdr:colOff><xdr:row>0</xdr:row><xdr:rowOff>0</xdr:rowOff></xdr:from><xdr:ext cx="' + w + '" cy="' + h + '"/><xdr:pic><xdr:nvPicPr><xdr:cNvPr id="1" name="Chart"/><xdr:cNvPicPr/></xdr:nvPicPr><xdr:blipFill><a:blip r:embed="rId1"/><a:stretch><a:fillRect/></a:stretch></xdr:blipFill><xdr:spPr><a:prstGeom prst="rect"><a:avLst/></a:prstGeom></xdr:spPr></xdr:pic><xdr:clientData/></xdr:oneCellAnchor></xdr:wsDr>');
        }
        return zip.generateAsync({type:'blob', mimeType:'application/vnd.openxmlformats-officedocument.' + (format === 'docx' ? 'wordprocessingml.document' : 'spreadsheetml.sheet')});
    }
    function chartImage(chart) {
        chart.stop(); chart.update(0);
        var output = document.createElement('canvas');
        output.width = chart.canvas.width; output.height = chart.canvas.height;
        var ctx = output.getContext('2d'); ctx.fillStyle = '#fff'; ctx.fillRect(0,0,output.width,output.height); ctx.drawImage(chart.canvas,0,0);
        return output;
    }
    // A4 text width in twips at 0.75in margins.
    var WORD_TEXT_WIDTH = 9746;
    function wordRun(text, props) {
        return '<w:r>' + (props ? '<w:rPr>' + props + '</w:rPr>' : '') + String(text == null ? '' : text).split(/\r\n|\r|\n/).map(function (line, i) {
            return (i ? '<w:br/>' : '') + '<w:t xml:space="preserve">' + xml(line) + '</w:t>';
        }).join('') + '</w:r>';
    }
    function wordPara(runs, props) {
        return '<w:p>' + (props ? '<w:pPr>' + props + '</w:pPr>' : '') + runs + '</w:p>';
    }
    function wordTable(rows, widths) {
        var sum = widths.reduce(function (total, width) { return total + width; }, 0);
        var columns = widths.map(function (width) { return Math.floor(width * WORD_TEXT_WIDTH / sum); });
        var borders = ['top','left','bottom','right','insideH','insideV'].map(function (edge) {
            return '<w:' + edge + ' w:val="single" w:sz="4" w:space="0" w:color="D9C5CB"/>';
        }).join('');
        return '<w:tbl><w:tblPr><w:tblW w:w="' + WORD_TEXT_WIDTH + '" w:type="dxa"/><w:tblBorders>' + borders + '</w:tblBorders><w:tblLayout w:type="fixed"/><w:tblCellMar><w:left w:w="80" w:type="dxa"/><w:right w:w="80" w:type="dxa"/></w:tblCellMar></w:tblPr><w:tblGrid>' + columns.map(function (width) {
            return '<w:gridCol w:w="' + width + '"/>';
        }).join('') + '</w:tblGrid>' + rows.map(function (row, r) {
            return '<w:tr><w:trPr><w:cantSplit/>' + (r ? '' : '<w:tblHeader/>') + '</w:trPr>' + row.map(function (cell, c) {
                return '<w:tc><w:tcPr><w:tcW w:w="' + columns[c] + '" w:type="dxa"/>' + (r ? '' : '<w:shd w:val="clear" w:color="auto" w:fill="F5E9ED"/>') + '</w:tcPr>'
                    + wordPara(wordRun(cell == null ? '—' : cell, (r ? '' : '<w:b/>') + '<w:sz w:val="20"/>'), '<w:spacing w:before="40" w:after="40"/>') + '</w:tc>';
            }).join('') + '</w:tr>';
        }).join('') + '</w:tbl>' + wordPara('');
    }
    function wordImage(relation, index, width, height) {
        var w = WORD_TEXT_WIDTH * 635, h = Math.round(w * height / width);
        if (h > 7315200) { w = Math.round(w * 7315200 / h); h = 7315200; }
        return wordPara('<w:r><w:drawing><wp:inline><wp:extent cx="' + w + '" cy="' + h + '"/><wp:docPr id="' + index + '" name="Chart ' + index + '"/><a:graphic><a:graphicData uri="' + ns + 'drawingml/2006/picture"><pic:pic><pic:nvPicPr><pic:cNvPr id="' + index + '" name="Chart ' + index + '"/><pic:cNvPicPr/></pic:nvPicPr><pic:blipFill><a:blip r:embed="' + relation + '"/><a:stretch><a:fillRect/></a:stretch></pic:blipFill><pic:spPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="' + w + '" cy="' + h + '"/></a:xfrm><a:prstGeom prst="rect"><a:avLst/></a:prstGeom></pic:spPr></pic:pic></a:graphicData></a:graphic></wp:inline></w:drawing></w:r>');
    }
    /* Builds a multi-section report. Blocks: {title}, {heading}, {subheading}, {text},
       {label, text}, {table, widths}, {chart}. */
    window.downloadWordReport = async function (name, blocks) {
        var zip = new JSZip(), relations = [['rId1','styles','styles.xml']], images = 0, body = '';
        blocks.forEach(function (block) {
            if (block.title) {
                body += wordPara(wordRun(block.title, '<w:b/><w:color w:val="3B0718"/><w:sz w:val="36"/>'), '<w:outlineLvl w:val="0"/>');
            } else if (block.heading) {
                body += wordPara(wordRun(block.heading, '<w:b/><w:color w:val="800020"/><w:sz w:val="26"/>'), '<w:keepNext/><w:spacing w:before="240" w:after="100"/><w:outlineLvl w:val="1"/>');
            } else if (block.subheading) {
                body += wordPara(wordRun(block.subheading, '<w:b/><w:color w:val="3B0718"/>'), '<w:keepNext/><w:spacing w:before="160" w:after="60"/><w:outlineLvl w:val="2"/>');
            } else if (block.table) {
                body += wordTable(block.table, block.widths);
            } else if (block.chart) {
                var image = chartImage(block.chart), relation = 'rId' + (relations.length + 1);
                images++;
                relations.push([relation, 'image', 'media/chart' + images + '.png']);
                zip.file('word/media/chart' + images + '.png', image.toDataURL('image/png').split(',')[1], {base64:true});
                body += wordImage(relation, images, image.width, image.height);
            } else if (block.label) {
                body += wordPara(wordRun(block.label + ': ', '<w:b/>') + wordRun(block.text));
            } else {
                body += wordPara(wordRun(block.text));
            }
        });
        zip.file('[Content_Types].xml', types([['word/document.xml','wordprocessingml.document.main'],['word/styles.xml','wordprocessingml.styles']]));
        zip.file('_rels/.rels', rels([['rId1','officeDocument','word/document.xml']]));
        zip.file('word/_rels/document.xml.rels', rels(relations));
        zip.file('word/styles.xml', '<?xml version="1.0" encoding="UTF-8"?><w:styles xmlns:w="' + ns + 'wordprocessingml/2006/main"><w:docDefaults><w:rPrDefault><w:rPr><w:rFonts w:ascii="Calibri" w:hAnsi="Calibri" w:cs="Calibri"/><w:sz w:val="22"/></w:rPr></w:rPrDefault><w:pPrDefault><w:pPr><w:spacing w:after="100"/></w:pPr></w:pPrDefault></w:docDefaults></w:styles>');
        zip.file('word/document.xml', '<?xml version="1.0" encoding="UTF-8"?><w:document xmlns:w="' + ns + 'wordprocessingml/2006/main" xmlns:r="' + ns + 'officeDocument/2006/relationships" xmlns:wp="' + ns + 'drawingml/2006/wordprocessingDrawing" xmlns:a="' + ns + 'drawingml/2006/main" xmlns:pic="' + ns + 'drawingml/2006/picture"><w:body>' + body + '<w:sectPr><w:pgSz w:w="11906" w:h="16838"/><w:pgMar w:top="1080" w:right="1080" w:bottom="1080" w:left="1080" w:header="720" w:footer="720" w:gutter="0"/></w:sectPr></w:body></w:document>');
        save(await zip.generateAsync({type:'blob', mimeType:'application/vnd.openxmlformats-officedocument.wordprocessingml.document'}), name + '.docx');
    };
    window.addChartExports = function (chart, title, context, rows) {
        var canvas = chart.canvas, toolbar = document.createElement('div');
        toolbar.className = 'chart-export-controls';
        toolbar.style.cssText = 'display:flex;gap:8px;flex-wrap:wrap;margin:0 0 16px';
        toolbar.setAttribute('role', 'group'); toolbar.setAttribute('aria-label', 'Export ' + title);
        var status = document.createElement('span'); status.setAttribute('role', 'status');
        var select = document.createElement('select');
        select.className = 'custom-select custom-select-sm';
        select.style.width = 'auto';
        select.setAttribute('aria-label', 'Export ' + title);
        var placeholder = document.createElement('option');
        placeholder.value = ''; placeholder.textContent = 'Export chart…';
        select.appendChild(placeholder);
        ['PDF','Word','Excel','Image'].forEach(function (label) {
            var option = document.createElement('option');
            option.value = label;
            option.textContent = {PDF:'PDF (.pdf)', Word:'Word (.docx)', Excel:'Excel (.xlsx)', Image:'Image (.png)'}[label];
            select.appendChild(option);
        });
        select.addEventListener('change', async function () {
                var label = select.value;
                if (!label) return;
                select.disabled = true; status.textContent = 'Preparing export…';
                try {
                    var output = chartImage(chart);
                    var png = output.toDataURL('image/png');
                    var name = (title + '-' + context).toLowerCase().replace(/[^a-z0-9]+/g,'-');
                    if (label === 'Image') {
                        var blob = await new Promise(function (resolve) { output.toBlob(resolve, 'image/png'); });
                        save(blob, name + '.png');
                    } else if (label === 'PDF') {
                        pdfMake.createPdf({pageSize:'A4', pageOrientation:'landscape', content:[{text:title,fontSize:18,margin:[0,0,0,8]},{text:context,margin:[0,0,0,12]},{image:png,fit:[750,440]}]}).download(name + '.pdf');
                    } else {
                        var ext = label === 'Word' ? 'docx' : 'xlsx';
                        save(await office(ext,title,context,png,output.width,output.height,rows),name + '.' + ext);
                    }
                    status.textContent = 'Export ready.';
                } catch (error) { status.textContent = 'Export failed. Please reload and try again.'; console.error(error); }
                finally { select.disabled = false; select.value = ''; }
        });
        toolbar.appendChild(select);
        toolbar.appendChild(status); canvas.parentNode.parentNode.insertBefore(toolbar, canvas.parentNode);
    };
}());
