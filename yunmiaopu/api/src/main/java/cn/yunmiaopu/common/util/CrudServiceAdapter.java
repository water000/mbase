package cn.yunmiaopu.common.util;

import org.springframework.data.repository.CrudRepository;

import java.util.Optional;

/**
 * Created by a on 2017/12/5.
 */
public class CrudServiceAdapter<T, ID> implements CrudServiceTemplete<T, ID> {
    private CrudRepository repo;

    public void setRepository(CrudRepository repo){
        this.repo = repo;
    }

    public CrudRepository getRepository(){
        return this.repo;
    }

    public <S extends T> S save(S var1){
        return (S)repo.save(var1);
    }

    public <S extends T> Iterable<S> saveAll(Iterable<S> var1){
        return repo.saveAll(var1);
    }

    public Optional<T> findById(ID var1){
        return repo.findById(var1);
    }

    public boolean existsById(ID var1){
        return repo.existsById(var1);
    }

    public Iterable<T> findAll(){
        return repo.findAll();
    }

    public Iterable<T> findAllById(Iterable<ID> var1){
        return repo.findAllById(var1);
    }

    public long count(){
        return repo.count();
    }

    public void deleteById(ID var1){
        repo.deleteById(var1);
    }

    public void delete(T var1){
        repo.delete(var1);
    }

    public void deleteAll(Iterable<? extends T> var1){
        repo.deleteAll(var1);
    }

    public void deleteAll(){
        repo.deleteAll();
    }


}
