package cn.yunmiaopu.category.controller;

import cn.yunmiaopu.category.entity.Attribute;
import cn.yunmiaopu.category.entity.Option;
import cn.yunmiaopu.category.service.IAttributeService;
import cn.yunmiaopu.category.service.IOptionService;
import cn.yunmiaopu.category.utli.UploadAttributeColor;
import cn.yunmiaopu.common.util.Response;
import com.alibaba.fastjson.JSON;
import com.alibaba.fastjson.JSONArray;
import com.alibaba.fastjson.JSONObject;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.web.bind.annotation.*;
import org.springframework.web.multipart.MultipartFile;

import javax.annotation.PostConstruct;
import javax.servlet.ServletContext;
import java.util.*;

/**
 * Created by macbookpro on 2018/5/29.
 */
@RestController
public class AttributeController {

    @Autowired
    private IAttributeService srv;

    @Autowired
    private IOptionService optsrv;

    @Autowired
    private UploadAttributeColor uac;

    @Autowired
    private ServletContext ctx;

    @PostConstruct
    public void init(){
        uac.setRealRoot(ctx.getRealPath("/"));
    }

    private void _delImg(String token){
        Attribute.NamedColor color = null;
        try{
            color = Attribute.NamedColor.valueOf(token);
        } catch (Exception e){
            color = null;
        }
        if(null == color && !token.startsWith("#")){
            uac.setToken(token);
            uac.delete();
        }
    }

    @PostMapping("/category-attribute")
    public Response save(Attribute attr,
                           String options,
                           @RequestParam("colorImg[]")MultipartFile[] colorImg){
        byte optionsCounter = 0;

        List<Option> opts = null;
        List<Option> ignore = new ArrayList<Option>();

        if(attr.getType() == Attribute.Type.COLOR || attr.getType() == Attribute.Type.ENUM){
            if(options != null && options.length() > 0)
                opts = JSON.parseArray(options, Option.class);
            else
                opts = new ArrayList<Option>();

            if( 0 == attr.getId() ){
                optionsCounter = (byte)opts.size();
            }else{
                Iterator<Option> srcitr = optsrv.findByAttributeId(attr.getId()).iterator();

                for(; srcitr.hasNext(); ){
                    Option found = null;
                    Option cmp = srcitr.next();

                    for(Option next : opts){
                        if(next.getId() > 0 && next.getId() == cmp.getId()){
                            found = next;
                            break;
                        }
                    }

                    if(found != null){
                        if(cmp.getExtra() != null){
                            String extra = found.getExtra();
                            if(null == extra || 0 == extra.length()) {
                                _delImg(cmp.getExtra());
                            }else{
                                found.setExtra(cmp.getExtra());
                                if(found.equals(cmp)){// no need to update if absolutely equals
                                    ignore.add(found);
                                }
                            }
                        }
                        optionsCounter++;
                    }else{
                        if(cmp.getExtra() != null)
                            _delImg(cmp.getExtra());
                        srcitr.remove();
                        optsrv.deleteById(cmp.getId());
                    }
                }

                for(Option opt : opts ){
                    if(0 == opt.getId())
                        optionsCounter++;
                }
            }
        }


        attr.setOptionsCounter(optionsCounter);
        attr.setEditTs(System.currentTimeMillis()/1000);
        attr = (Attribute)srv.save(attr);

        if(opts != null && opts.size() > 0){
            int i=0;
            for(Option o : opts){
                if(ignore.contains(o))
                    continue;
                if(Attribute.Type.COLOR == attr.getType()
                        && colorImg != null
                        && colorImg.length > 0
                        && (null == o.getExtra() || 0 == o.getExtra().length())) {
                    try {
                        uac.resize(colorImg[i++].getInputStream());
                        o.setExtra(uac.getToken());
                    } catch (Exception e) {
                        continue;
                    }
                }
                o.setAttributeId(attr.getId());
                optsrv.save(o);
            }
        }

        JSONObject json = (JSONObject)JSON.toJSON(attr);
        json.put("options", opts);
        return Response.ok(json);
    }

    @GetMapping("/category-attribute/{categoryId}")
    public JSONObject get(@PathVariable long attributeId){
        Optional<Attribute> opt = srv.findById(new Long(attributeId));
        if(!opt.isPresent())
            throw new IllegalArgumentException("$attributeId not found");
        Attribute attr = opt.get();
        JSONObject json = (JSONObject)JSONObject.toJSON(attr);
        json.put("options", optsrv.findByAttributeId(attr.getId()));

        return json;
    }

    @GetMapping("/category-attributes/{categoryId}")
    public JSONArray list(@PathVariable long categoryId){
        JSONArray ret = new JSONArray();

        for(Attribute attr : srv.findByCategoryId(categoryId) ){
            JSONObject item = (JSONObject)JSON.toJSON(attr);
            item.put("options", optsrv.findByAttributeId(attr.getId()));
            ret.add(item);
        }

        return ret;
    }

    @DeleteMapping("/{attributeId}")
    public int delete(@PathVariable long categoryId){
        Optional<Attribute> opt = srv.findById(new Long(categoryId));
        if(!opt.isPresent()){
            throw new IllegalArgumentException("$categoryId not found");
        }
        srv.delete(opt.get());
        return 0;
    }

    @PostMapping("/category-attribute-reorder")
    public int reorder(String json){
        int res = 0;

        JSONArray arr = JSONArray.parseArray(json);
        if(arr != null && arr.size() > 0){
            long categoryId = arr.getLongValue(0);
            JSONArray attrs = arr.getJSONArray(1);
            if(categoryId > 0 && attrs != null && attrs.size() > 0){
                List<long[]> attrToReorder = new ArrayList();
                List<long[][]> optToReorder  = new ArrayList();

                for(int i=0; i<attrs.size(); i++){
                    JSONArray attr = attrs.getJSONArray(i);
                    if(attr != null && 3 == attr.size()){
                        byte seq = attr.getByteValue(1);
                        long attributeId = attr.getLongValue(0);
                        if(seq != -99){
                            attrToReorder.add(new long[]{attributeId, i+1});
                        }

                        JSONArray opts = attr.getJSONArray(2);
                        if(opts != null && opts.size()>0){
                            int j;
                            long[][] seqmeta = new long[opts.size()][];
                            for(j=0; j<opts.size(); j++){
                                JSONArray opt = opts.getJSONArray(j);
                                if(opt != null && 2 == opt.size()){
                                    long optId = opt.getLongValue(0);
                                    byte optSeq = opt.getByteValue(1);
                                    seqmeta[j] = new long[]{attributeId, optId, j+1};
                                }
                            }
                            optToReorder.add(seqmeta);
                        }
                    }
                }

                boolean found = false;

                if(attrToReorder.size() > 0) {
                    Iterable<Attribute> attritr = srv.findByCategoryId(categoryId);
                    for (Attribute a : attritr) {
                        Iterator<long[]> attrSeqItr = attrToReorder.iterator();
                        found = false;
                        while (attrSeqItr.hasNext()) {
                            long[] seqmeta = attrSeqItr.next();
                            if (a.getId() == seqmeta[0]) {
                                found = true;
                                if (a.getSeq() == seqmeta[1])
                                    attrSeqItr.remove();
                                break;
                            }
                        }
                        if (!found) {
                            break;
                        }
                    }
                }else{
                    found = true;
                }

                if(found) {
                    for (long[][] optseq : optToReorder) {
                        Iterable<Option> optsitr = optsrv.findByAttributeId(optseq[0][0]);
                        found = false;
                        for (Option opt : optsitr) {
                            found = false;
                            for (long[] seqmeta : optseq) {
                                if (opt.getId() == seqmeta[1]) {
                                    found = true;
                                    if (seqmeta[2] == opt.getSeq()) {
                                        seqmeta[2] = -99;
                                    }
                                    break;
                                }
                            }
                            if (!found)
                                break;
                        }
                        if (!found)
                            break;
                    }

                    if(found){
                        for(long[] a : attrToReorder){
                            srv.updateSeqById((byte)a[1], a[0], System.currentTimeMillis()/1000);
                        }
                        for(long[][] o : optToReorder){
                            for(long[] seq : o) {
                                if(seq[2] != -99)
                                    optsrv.updateSeqById((byte)seq[2], seq[1]);
                            }
                        }

                        res = attrToReorder.size() + optToReorder.size();
                    }
                }

            }
        }

        return res;
    }

}
