/* Local chart exports: pdfMake and JSZip are bundled with the application. */
(function () {
    'use strict';
    var ns = 'http://schemas.openxmlformats.org/';
    function xml(value) {
        return String(value == null ? '' : value).replace(/[&<>"']/g, function (c) {
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
                    chart.stop(); chart.update(0);
                    var output = document.createElement('canvas');
                    output.width = canvas.width; output.height = canvas.height;
                    var ctx = output.getContext('2d'); ctx.fillStyle = '#fff'; ctx.fillRect(0,0,output.width,output.height); ctx.drawImage(canvas,0,0);
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
